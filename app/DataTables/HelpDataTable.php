<?php

/**
 * Help DataTable
 *
 * @package     Makent
 * @subpackage  DataTable
 * @category    Help
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use App\Models\Help;
use DB;

class HelpDataTable extends DataTable
{    
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('answer', function ($help) {
                return $help->answer;
            })
            ->addColumn('action', function ($help) {
                $edit = '<a href="'.url(ADMIN_URL.'/edit_help/'.$help->help_id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>';

                $delete = '<a data-href="'.url(ADMIN_URL.'/delete_help/'.$help->help_id).'" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#confirm-delete"><i class="glyphicon glyphicon-trash"></i></a>';

                return $edit.'&nbsp;'.$delete;
            })
            ->rawColumns(['answer','action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Help $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Help $model)
    {
        $help = $model::join('help_category', function($join) {
                                $join->on('help_category.id', '=', 'help.category_id');
                            })
                    ->leftJoin('help_subcategory', function($join) {
                                $join->on('help_subcategory.id', '=', 'help.subcategory_id');
                            })
                    ->select(['help_category.*', 'help_subcategory.*','help.id as help_id','help.category_id as category_id','help.subcategory_id as subcategory_id','question',DB::raw("concat(substring(answer, 1, 50),'...') as answer"),'help.status as help_status']);
        return $help;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->columns($this->getColumns())
                    ->addAction(["printable" => false])
                    ->minifiedAjax()
                    ->dom('lBfr<"table-responsive"t>ip')
                    ->orderBy(0)
                    ->buttons(
                        ['csv','excel', 'print', 'reset']
                    );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return array(
            ['data' => 'help_id', 'name' => 'help.id', 'title' => 'Id'],
            ['data' => 'category_name', 'name' => 'help_category.name', 'title' => 'Category Name'],
            ['data' => 'subcategory_name', 'name' => 'help_subcategory.name', 'title' => 'Subcategory Name'],
            ['data' => 'question', 'name' => 'help.question', 'title' => 'Question'],
            ['data' => 'answer', 'name' => 'help.answer', 'title' => 'Answer'],
            ['data' => 'help_status', 'name' => 'help.status', 'title' => 'Status'],
        );
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'help_' . date('YmdHis');
    }
}