<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDO;

class Assessment extends Model
{

    protected $table = 'ERPRAK.VW_ASSMNTAPP_STD_LIST';
    protected $connection = 'oracle';

    protected $casts = [
        'testdate' => 'date:Y-m-d'
    ];

    protected $appends = [
        'student_name', 'allow_result'
    ];

    public function getStudentNameAttribute($value)
    {
        return Str::limit($this->studname, 15);
    }

    public function getAllowResultAttribute($value)
    {
        return false;
    }

    public function getStdphotoAttribute($value)
    {
        return base64_encode($value);
    }


    public function classDetail($student_id)
    {
        try {
            $sql = "SELECT SERIALNO, COLUMN0 AS CLASS_FLAG, CASE WHEN COLUMN0 ='C'  THEN 'Class' WHEN COLUMN0 = 'N'  THEN 'Night Classes' WHEN COLUMN0 = 'H'  THEN 'Highway classes' ELSE COLUMN0 END AS CLASS_TYPE, COLUMN1 AS INSTRUCTOR_ID, COLUMN2 AS INSTRUCTOR_NAME, COLUMN3 AS NO_OF_CLASS, COLUMN4 AS CLASS_DATE FROM TABLE(ERPRAK.DFN_STD_CLASS_DETAILS('$student_id'))";
            return collect(DB::connection($this->connection)->select($sql));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function idAssessmentDetail($student_id)
    {
        try {
            $sql = "SELECT SERIALNO, COLUMN0 AS HEADER_CODE, COLUMN1 AS HEADER_TITLE, COLUMN2 AS ATTEMPT, COLUMN3 AS ASSESSOR_ID, COLUMN4 AS ASSESSOR_NAME, COLUMN5 AS INSTRUCTOR_ID, COLUMN6 AS INSTRUCTOR_NAME, COLUMN7 AS TEST_DATE, COLUMN8 AS NO_OF_MAJOR_MISTAKE, COLUMN9 AS NO_OF_MINOR_MISTAKE, COLUMN10 as RESULT, COLUMN11 as CLASSES FROM TABLE(ERPRAK.DFN_STD_GRA_TEST_DETAILS('$student_id','006'))";
            return collect(DB::connection($this->connection)->select($sql));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function reAssessmentDetail($student_id)
    {
        try {
            $sql = "SELECT SERIALNO, COLUMN0 AS HEADER_CODE, COLUMN1 AS HEADER_TITLE, COLUMN2 AS ATTEMPT, COLUMN3 AS ASSESSOR_ID, COLUMN4 AS ASSESSOR_NAME, COLUMN5 AS INSTRUCTOR_ID, COLUMN6 AS INSTRUCTOR_NAME, COLUMN7 AS TEST_DATE, COLUMN8 AS NO_OF_MAJOR_MISTAKE, COLUMN9 AS NO_OF_MINOR_MISTAKE, COLUMN10 as RESULT, COLUMN11 as CLASSES FROM TABLE(ERPRAK.DFN_STD_GRA_TEST_DETAILS('$student_id','007'))";
            return collect(DB::connection($this->connection)->select($sql));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function evaluationDetail($student_id)
    {
        try {
            $sql = "SELECT SERIALNO, COLUMN0 AS HEADER_CODE, COLUMN1 AS HEADER_TITLE, COLUMN2 AS ATTEMPT, COLUMN3 AS ASSESSOR_ID, COLUMN4 AS ASSESSOR_NAME, COLUMN5 AS INSTRUCTOR_ID, COLUMN6 AS INSTRUCTOR_NAME, COLUMN7 AS TEST_DATE, COLUMN8 AS NO_OF_MAJOR_MISTAKE, COLUMN9 AS NO_OF_MINOR_MISTAKE, COLUMN10 as RESULT, COLUMN11 as CLASSES FROM TABLE(ERPRAK.DFN_STD_GRA_TEST_DETAILS('$student_id','008'))";
            return collect(DB::connection($this->connection)->select($sql));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function assessmentQuestionDetail($student_id, $test_code, $attempt_no)
    {
        try {
            $sql = "SELECT Column0 AS SLNO, Column1 AS TEST_CODE, Column2 AS TEST_NAME, Column3 AS MISTAKES_CODE, Column4 AS MISTAKES_CATEGORY, Column5 AS MISTAKES_TYPE, Column6 AS MISTAKES_DETAILS, Column7 AS NO_OF_MISTAKE FROM TABLE(ERPRAK.DFN_STD_EXAM_MISTAKES('$student_id','$test_code', '$attempt_no'))";
            return collect(DB::connection($this->connection)->select($sql));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function assessmentPostDetail($student_id, $test_date, $result, $mistake = null, $remarks = null)
    {
        try {
            $status = '';
            $message = '';

            $stmt = DB::connection($this->connection)->getPdo()->prepare("BEGIN BDCUSER.DPR_ASSMNT_APP_POST_DETAILS (:iRegnnumb, :iTestDate, :iMistakeDetails, :iResultDetails, :iRemarks, :iStatus, :iStMsg); END;");
            $stmt->bindParam(':iRegnnumb', $student_id);
            $stmt->bindParam(':iTestDate', $test_date);
            $stmt->bindParam(':iMistakeDetails', $mistake);
            $stmt->bindParam(':iResultDetails', $result);
            $stmt->bindParam(':iRemarks', $remarks);
            $stmt->bindParam(':iStatus', $status, PDO::PARAM_INPUT_OUTPUT);
            $stmt->bindParam(':iStMsg', $message, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 300);
            $stmt->execute();

            return collect(['status' => $status, 'message' => $message]);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}
