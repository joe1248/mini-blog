<?php

namespace App\Extensions\Doctrine;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * "MATCH_AGAINST" "(" {StateFieldPathExpression ","}* InParameter {Literal}? ")"
 */
class MatchAgainst extends FunctionNode {

    public $columns = array();
    public $needle;
    public $mode;

    /**
     * @param Parser $parser
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        do {
            $this->columns[] = $parser->StateFieldPathExpression();
            $parser->match(Lexer::T_COMMA);
        }
        while (!$parser->getLexer()->isNextToken(Lexer::T_INPUT_PARAMETER));

        // Got an input parameter
        $this->needle = $parser->InputParameter();

        while ($parser->getLexer()->isNextToken(Lexer::T_STRING)) {
            $this->mode = $parser->Literal();
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker) {
        $haystack = null;
        $first = true;
        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ', ';
            $haystack .= $column->dispatch($sqlWalker);
        }
        $query = "MATCH(" . $haystack .
            ") AGAINST (" . $this->needle->dispatch($sqlWalker);
        if ($this->mode) {
            $query .= " " . $this->mode->dispatch($sqlWalker) . " )";
        } else {
            $query .= " )";
        }

        return $query;
    }
}