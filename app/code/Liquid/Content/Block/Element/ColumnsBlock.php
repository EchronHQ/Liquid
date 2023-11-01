<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Core\Model\Layout\AbstractBlock;

class ColumnsBlock extends AbstractBlock
{
    /** @var AbstractBlock[] */
    private array $columns = [];

    private string $columnLayout = '';

    /**
     * @var string
     * columnGapExtraLarge
     */
    private string $gapSize = '';

    public function toHtml(): string
    {
        $output = '<div class="column ' . $this->columnLayout . ' ' . $this->gapSize . '">';

        foreach ($this->columns as $column) {
            $output .= $column->toHtml();
        }
        $output .= '</div>';

        return $output;
    }

    public function setGapSize(string $size): void
    {
        $this->gapSize = $size;
    }

    public function addColumn(AbstractBlock $column): void
    {
        $this->columns[] = $column;
    }


    public function create_1_1_1_1(AbstractBlock $col1, AbstractBlock $col2, AbstractBlock $col3, AbstractBlock $col4): void
    {
        $this->columnLayout = 'col-1-1-1-1';
        $this->columns[] = $col1;
        $this->columns[] = $col2;
        $this->columns[] = $col3;
        $this->columns[] = $col4;

    }

    public function create_2_2(AbstractBlock $col1, AbstractBlock $col2): void
    {
        $this->columnLayout = 'col-2-2';
        $this->columns[] = $col1;
        $this->columns[] = $col2;

    }

}
