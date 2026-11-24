<?php declare(strict_types=1);

namespace App\Database\Query;

use Doctrine\ORM\Query\SqlWalker;

/**
 * TS_RANK(document, query): the relevance of the document for the query (PostgreSQL ts_rank, see FullTextFunction), to sort by.
 */
class TsRankFunction extends FullTextFunction
{
    public function getSql(SqlWalker $sqlWalker) : string
    {
        return 'ts_rank(' . $this->vector($sqlWalker) . ', ' . $this->tsQuery($sqlWalker) . ')';
    }
}
