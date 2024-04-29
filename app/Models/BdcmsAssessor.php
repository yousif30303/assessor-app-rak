<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BdcmsAssessor extends Model
{

    protected $connection = 'oracle';
    protected $table = 'ERPRAK.DI_LK_TREXMMAS';

    public function getAccessor($sb_id)
    {
        try {
            $sql = "SELECT EXEMPCDE, INITCAP (EXEMPNAM) name, DECODE (GENDCODE,  'M', 'Male',  'F', 'Female')  Gender, ERPRAK.DFN_DI_CodeDesc ('DI_MS_CODEMAST', 'NTN', SUBSTR (NATIONAL, 4), 'E') NATIONALITY, ERPRAK.DFN_DI_CodeDesc ('DI_MS_CODEMAST','STS', SUBSTR (STATUSCD, 4), 'E') STATUS FROM ERPRAK.DI_LK_TREXMMAS WHERE COMPCODE = '100' AND EXEMPCDE = '$sb_id'";
            return DB::connection($this->connection)->select($sql);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}
