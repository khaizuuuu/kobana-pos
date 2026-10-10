<?php

namespace App\Services;

use App\Models\FpAnalysis;
use App\Models\FpFrequentItemset;
use App\Models\FpAssociationRule;
use App\Models\TransactionItem;
use Carbon\Carbon;

/**
 * FP-Growth Market Basket Analysis – pure PHP implementation.
 *
 * Pipeline:
 *   1. Load transactions (grouped list of menu IDs).
 *   2. Count item frequencies → remove items below minSupport.
 *   3. Sort each transaction by frequency descending.
 *   4. Build FP-Tree.
 *   5. Mine frequent itemsets via conditional pattern bases.
 *   6. Generate association rules (support, confidence, lift).
 *   7. Persist results to fp_frequent_itemsets & fp_association_rules.
 */
class FpGrowthService
{
    protected float $minSupport;
    protected float $minConfidence;
    protected ?Carbon $dateFrom = null;
    protected ?Carbon $dateTo   = null;

    public function __construct(float $minSupport = 0.05, float $minConfidence = 0.6)
    {
        $this->minSupport    = $minSupport;
        $this->minConfidence = $minConfidence;
    }

    public function setDateRange(?Carbon $from, ?Carbon $to): self
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
        return $this;
    }

    /**
     * Run the full FP-Growth pipeline and persist the results.
     */
    public function run(FpAnalysis $analysis): void
    {
        // 1. Load raw transactions
        $rawTransactions = $this->loadTransactions();
        $totalTrx = count($rawTransactions);

        // Update total_transactions column (exists in the original migration)
        $analysis->update(['total_transactions' => $totalTrx]);

        if ($totalTrx === 0) {
            return;
        }

        $minSupportCount = (int) ceil($this->minSupport * $totalTrx);

        // 2. Count 1-item frequencies
        $freq = [];
        foreach ($rawTransactions as $items) {
            foreach ($items as $item) {
                $freq[$item] = ($freq[$item] ?? 0) + 1;
            }
        }

        // Remove infrequent items
        $freq = array_filter($freq, fn($c) => $c >= $minSupportCount);
        arsort($freq);

        if (empty($freq)) {
            return;
        }

        // 3. Sort each transaction by descending frequency
        $transactions = [];
        foreach ($rawTransactions as $items) {
            $filtered = array_filter($items, fn($i) => isset($freq[$i]));
            usort($filtered, fn($a, $b) => $freq[$b] <=> $freq[$a]);
            if (!empty($filtered)) {
                $transactions[] = array_values($filtered);
            }
        }

        // 4. Build FP-Tree
        $tree = new FpTreeNode();
        foreach ($transactions as $transaction) {
            $this->insertTree($transaction, $tree);
        }

        // Build header table
        $headerTable = [];
        foreach (array_keys($freq) as $item) {
            $headerTable[$item] = [];
        }
        $this->buildHeaderTable($tree, $headerTable);

        // 5. Mine frequent itemsets
        $freqItemsets = [];
        $this->mineTree($headerTable, $minSupportCount, [], $transactions, $freqItemsets);

        // Also include single items
        foreach ($freq as $item => $count) {
            $freqItemsets[] = [
                'items'   => [$item],
                'support' => $count,
            ];
        }

        // Deduplicate
        $unique = [];
        foreach ($freqItemsets as $set) {
            $sorted = $set['items'];
            sort($sorted);
            $key = implode(',', $sorted);
            if (!isset($unique[$key]) || $unique[$key]['support'] < $set['support']) {
                $unique[$key] = ['items' => $sorted, 'support' => $set['support']];
            }
        }
        $freqItemsets = array_values(array_filter($unique, fn($s) => $s['support'] >= $minSupportCount));

        // Persist frequent itemsets
        foreach ($freqItemsets as $set) {
            FpFrequentItemset::create([
                'analysis_id'   => $analysis->id,
                'items'         => $set['items'],
                'items_label'   => implode(', ', $set['items']),
                'itemset_size'  => count($set['items']),
                'support_count' => $set['support'],
                'support'       => round($set['support'] / $totalTrx, 4),
            ]);
        }

        // 6. Generate association rules
        foreach ($freqItemsets as $set) {
            if (count($set['items']) < 2) {
                continue;
            }
            $this->generateRules($set['items'], $freqItemsets, $totalTrx, $analysis->id);
        }
    }

    // ─── FP-Tree helpers ──────────────────────────────────────────────────

    private function insertTree(array $transaction, FpTreeNode $tree): void
    {
        $node = $tree;
        foreach ($transaction as $item) {
            if (!isset($node->children[$item])) {
                $node->children[$item] = new FpTreeNode($item, $node);
            }
            $node->children[$item]->count++;
            $node = $node->children[$item];
        }
    }

    private function buildHeaderTable(FpTreeNode $node, array &$headerTable): void
    {
        foreach ($node->children as $item => $child) {
            if (isset($headerTable[$item])) {
                $headerTable[$item][] = $child;
            }
            $this->buildHeaderTable($child, $headerTable);
        }
    }

    private function mineTree(
        array  $headerTable,
        int    $minCount,
        array  $suffix,
        array  $transactions,
        array  &$result
    ): void {
        foreach (array_reverse(array_keys($headerTable)) as $item) {
            $itemset = array_merge([$item], $suffix);
            $support = array_sum(array_map(fn($n) => $n->count, $headerTable[$item]));

            if ($support < $minCount) {
                continue;
            }

            if (count($itemset) > 1) {
                $result[] = ['items' => $itemset, 'support' => $support];
            }

            // Conditional pattern base
            $conditionalPatterns = [];
            foreach ($headerTable[$item] as $node) {
                $path   = [];
                $parent = $node->parent;
                while ($parent && $parent->item !== null) {
                    $path[] = $parent->item;
                    $parent = $parent->parent;
                }
                if (!empty($path)) {
                    for ($i = 0; $i < $node->count; $i++) {
                        $conditionalPatterns[] = array_reverse($path);
                    }
                }
            }

            if (empty($conditionalPatterns)) {
                continue;
            }

            // Frequency count in conditional patterns
            $condFreq = [];
            foreach ($conditionalPatterns as $p) {
                foreach ($p as $pItem) {
                    $condFreq[$pItem] = ($condFreq[$pItem] ?? 0) + 1;
                }
            }
            $condFreq = array_filter($condFreq, fn($c) => $c >= $minCount);
            if (empty($condFreq)) {
                continue;
            }
            arsort($condFreq);

            $condTree = new FpTreeNode();
            $condHeader = array_fill_keys(array_keys($condFreq), []);
            foreach ($conditionalPatterns as $p) {
                $filtered = array_filter($p, fn($i) => isset($condFreq[$i]));
                usort($filtered, fn($a, $b) => $condFreq[$b] <=> $condFreq[$a]);
                $this->insertTree(array_values($filtered), $condTree);
            }
            $this->buildHeaderTable($condTree, $condHeader);
            $this->mineTree($condHeader, $minCount, $itemset, $conditionalPatterns, $result);
        }
    }

    // ─── Association Rules ────────────────────────────────────────────────

    private function generateRules(
        array  $items,
        array  $allItemsets,
        int    $totalTrx,
        int    $analysisId
    ): void {
        $n = count($items);
        $supportAB = $this->findSupport($items, $allItemsets);

        for ($mask = 1; $mask < (1 << $n) - 1; $mask++) {
            $antecedent = [];
            $consequent = [];
            for ($j = 0; $j < $n; $j++) {
                if ($mask & (1 << $j)) {
                    $antecedent[] = $items[$j];
                } else {
                    $consequent[] = $items[$j];
                }
            }
            if (empty($consequent)) {
                continue;
            }

            $supportA = $this->findSupport($antecedent, $allItemsets);
            $supportB = $this->findSupport($consequent, $allItemsets);

            if ($supportA === 0 || $supportB === 0) {
                continue;
            }

            $confidence = $supportAB / $supportA;
            if ($confidence < $this->minConfidence) {
                continue;
            }

            $lift = $confidence / ($supportB / $totalTrx);

            FpAssociationRule::create([
                'analysis_id'      => $analysisId,
                'antecedent'       => $antecedent,
                'consequent'       => $consequent,
                'antecedent_label' => implode(', ', $antecedent),
                'consequent_label' => implode(', ', $consequent),
                'support'          => round($supportAB / $totalTrx, 4),
                'confidence'       => round($confidence, 4),
                'lift'             => round($lift, 4),
            ]);
        }
    }

    private function findSupport(array $target, array $allItemsets): int
    {
        $sorted = $target;
        sort($sorted);
        foreach ($allItemsets as $set) {
            $s = $set['items'];
            sort($s);
            if ($s === $sorted) {
                return $set['support'];
            }
        }
        return 0;
    }

    // ─── DB loader ────────────────────────────────────────────────────────

    private function loadTransactions(): array
    {
        $query = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->select('transactions.id as trx_id', 'transaction_items.menu_id');

        if ($this->dateFrom) {
            $query->whereDate('transactions.transaction_date', '>=', $this->dateFrom->toDateString());
        }
        if ($this->dateTo) {
            $query->whereDate('transactions.transaction_date', '<=', $this->dateTo->toDateString());
        }

        $rows         = $query->orderBy('trx_id')->get();
        $transactions = [];
        foreach ($rows as $row) {
            $transactions[$row->trx_id][] = (int) $row->menu_id;
        }

        return array_map(fn($items) => array_values(array_unique($items)), array_values($transactions));
    }
}

/**
 * FP-Tree node.
 */
class FpTreeNode
{
    public int $count = 0;
    /** @var array<int, FpTreeNode> */
    public array $children = [];

    public function __construct(
        public readonly ?int $item   = null,
        public readonly ?self $parent = null
    ) {}
}
