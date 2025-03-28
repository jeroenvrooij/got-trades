<?php
namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class ArrayToStringFunction extends FunctionNode
{
    public $arrayExpression = null;
    public $delimiterExpression = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // "array_to_string"
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->arrayExpression = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_COMMA);
        $this->delimiterExpression = $parser->ArithmeticExpression();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'array_to_string(' .
            $this->arrayExpression->dispatch($sqlWalker) . ',' .
            $this->delimiterExpression->dispatch($sqlWalker) .
            ')';
    }
}
