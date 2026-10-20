<?php

namespace App\Model;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Pagination légère au-dessus du Paginator de Doctrine.
 *
 * @template T
 *
 * @implements \IteratorAggregate<int, T>
 */
final class Page implements \IteratorAggregate, \Countable
{
    /** @var list<T> */
    private array $resultats;
    private int $total;

    public function __construct(QueryBuilder $qb, public readonly int $numero, public readonly int $parPage = 12)
    {
        $qb->setFirstResult(($numero - 1) * $parPage)->setMaxResults($parPage);
        $paginator = new Paginator($qb, fetchJoinCollection: false);
        $this->total = \count($paginator);
        $this->resultats = iterator_to_array($paginator, false);
    }

    /** @return \ArrayIterator<int, T> */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->resultats);
    }

    public function count(): int
    {
        return \count($this->resultats);
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getNbPages(): int
    {
        return max(1, (int) ceil($this->total / $this->parPage));
    }

    public function aPrecedente(): bool
    {
        return $this->numero > 1;
    }

    public function aSuivante(): bool
    {
        return $this->numero < $this->getNbPages();
    }
}
