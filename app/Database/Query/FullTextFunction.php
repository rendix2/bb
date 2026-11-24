<?php declare(strict_types=1);

namespace App\Database\Query;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * The PostgreSQL full-text search in DQL (beberlei/doctrineextensions has no full-text function): FUNCTION(document, query),
 * the document a string expression of the entity, the query the text the user typed (websearch_to_tsquery: words, "phrases",
 * OR, -word; never a syntax error). Both with the text search configuration CONFIG.
 *
 * The SQL of the document must be the same expression as the GIN index of its table (the postUp() of the migrations of post
 * and topic), else PostgreSQL does not use the index: post CONCAT(COALESCE(_p.title, ''), ' ', _p.text), topic _t.name.
 */
abstract class FullTextFunction extends FunctionNode
{
    /**
     * 'simple': lower case, no stemming and no stop words. PostgreSQL has no Czech stemmer (snowball has none), and a Czech
     * dictionary (ispell) is not part of the image; 'simple' works the same for Czech and English. Diacritics are kept (unaccent
     * would need the extension and an IMMUTABLE wrapper function for the index).
     */
    public const string Config = 'simple';

    protected Node $document;

    protected Node $query;

    public function parse(Parser $parser) : void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->document = $parser->StringPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->query = $parser->StringPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    protected function vector(SqlWalker $sqlWalker) : string
    {
        return "to_tsvector('" . self::Config . "', " . $this->document->dispatch($sqlWalker) . ')';
    }

    protected function tsQuery(SqlWalker $sqlWalker) : string
    {
        return "websearch_to_tsquery('" . self::Config . "', " . $this->query->dispatch($sqlWalker) . ')';
    }
}
