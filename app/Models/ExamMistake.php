<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExamMistake extends Model
{
    protected $connection='oracle';
    protected $table;

    public function getMistakes($student_id, $date){
        try {
            $sql = "SELECT Column0 AS SLNO, Column1 AS TEST_CODE, Column2 AS TEST_NAME, Column3 AS MISTAKES_CODE, Column4 AS MISTAKES_CATEGORY, Column5 AS MISTAKES_TYPE, Column6 AS MISTAKES_DETAILS FROM TABLE(ERPRAK.DFN_EXAM_MISTAKE_LIST($student_id,'$date'))";
            return collect( DB::connection($this->connection)->select($sql));
        }
        catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

}
