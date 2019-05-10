<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CrudController extends Controller
{
    public static function montaTabela($headers, $contents, $classes = null)
    {
        if(isset($classes)) {
            $classes = implode(' ', $classes);
            $table = "<table class='".$classes."' />";
        } else {
            $table = "<table>";
        }
        $table .= "<thead>";
        
        foreach($headers as $header) {
            $table .= "<th>";
            $table .= $header;
            $table .= "</th>";
        }
        $table .= "</tr>";
        $table .= "</thead>";
        $table .= "<tbody>";
        foreach($contents as $content) {
            $table .= "<tr>";
            foreach($content as $single) {
                if($single === end($content))
                    $table .= "<td class='nowrap'>";
                else
                    $table .= "<td>";
                $table .= $single;
                $table .= "</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</tbody>";
        $table .= "</table>";

        return $table;
    }
}
