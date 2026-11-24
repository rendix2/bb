<?php declare(strict_types=1);

namespace App\Database\Query;

use Doctrine\ORM\Query\SqlWalker;

/**
 * TS_MATCH(document, query) = true: the document matches the query of the user (PostgreSQL `@@`, see FullTextFunction).
 */
class TsMatchFunction extends FullTextFunction
{
    public function getSql(SqlWalker $sqlWalker) : string
    {
        return '(' . $this->vector($sqlWalker) . ' @@ ' . $this->tsQuery($sqlWalker) . ')';
    }
}
