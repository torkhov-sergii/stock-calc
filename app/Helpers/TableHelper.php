<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;

class TableHelper
{
    static function generateTable(Collection $data, array $columns)
    {
        $return = '';

        $return .= '<table class="table"><tr>';

        foreach ($columns as $column) {
            $return .= '<th>'.$column.'</th>';
        }

        $return .= '</tr>';

        foreach ($data as $item) {
            $return .= '<tr>';

            foreach ($columns as $column) {
                $return .= '<td>'.$item[$column].'</td>';
            }

            $return .= '</tr>';
        }

        $return .= '</table>';

        return $return;
    }
}
