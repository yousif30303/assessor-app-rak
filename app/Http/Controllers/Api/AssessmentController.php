<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\AssessmentReport;
use App\Models\ExamMistake;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AssessmentController extends BaseController
{
    public $course_code;

    public function __construct()
    {
        $this->course_code = [
            'road' => ['006', '007', '008'],
            'yard' => ['009'],
            'preliminary' => ['010']
        ];
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);

            if ($validator->fails())
                return $this->errorWithData($validator->errors()->first(), $validator->errors());

            $date = Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay()->toDateTimeString();
            $data = new \stdClass();
            $data->course_code = $this->course_code;
            $data->assessment_count = Assessment::select('test_name', DB::raw('COUNT(regnnumb) as total'))
                ->where('EXEMPCDE', auth()->user()->sb_id)
                ->where('testdate', $date)->groupBy('test_name')
                ->get()->each(function ($row) {
                    $row->setHidden(['student_name']);
                });
            $data->assessments = Assessment::where('EXEMPCDE', auth()->user()->sb_id)->where('testdate', $date)->get();
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function assessmentQuestions(Request $request, $student_id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
            ]);
            if ($validator->fails())
                return $this->errorWithData($validator->errors()->first(), $validator->errors());
            $data = (new ExamMistake())->getMistakes($student_id, $request->date)->groupBy('test_name')->transform(function ($item, $k) {
                return $item->groupBy('mistakes_category')->transform(function ($item, $k) {
                    return $item->groupBy('mistakes_type');
                });
            });
            $date = Carbon::now()->startOfDay()->toDateTimeString();
            $assessment_detail=collect(Assessment::where('regnnumb', $request->student_id)->where('testdate', $date)->first()->toArray()) ?? null;
            $activityLog = ActivityLog::updateOrCreate(
                ['id' => 0],
                [
                    'student_id' => $student_id,
                    'sb_id' => auth()->user()->sb_id,
                    'request_data' => collect($request->all()),
                    'assessment_detail' => $assessment_detail,
                    'action' => "started",
                    'response_data' => $data,
                ]
            );
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function assessmentDetail($student_id)
    {
        try {
            $data = new \stdClass();
            $class = (new Assessment())->classDetail($student_id);
            $class_title = 'Classes';
            $data->course_code = $this->course_code;
            $data->course_code['class'] = $class_title;

            if (count($class))
                $data->$class_title = $class;
            $i_d_assessment = (new Assessment())->idAssessmentDetail($student_id)->map(function ($item) {
                $item->classes = (int)$item->classes;
                return $item;
            });
            if (count($i_d_assessment)) {
                $title = $i_d_assessment->first()->header_code;
                $data->$title = $i_d_assessment;
            }

            $re_assessment = (new Assessment())->reAssessmentDetail($student_id)->map(function ($item) {
                $item->classes = (int)$item->classes;
                return $item;
            });
            if (count($re_assessment)) {
                $title = $re_assessment->first()->header_code;
                $data->$title = $re_assessment;
            }

            $evaluation = (new Assessment())->evaluationDetail($student_id)->map(function ($item) {
                $item->classes = (int)$item->classes;
                return $item;
            });
            if (count($evaluation)) {
                $title = $evaluation->first()->header_code;
                $data->$title = $evaluation;
            }

            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function assessmentDetailQuestion($student_id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'test_code' => 'required',
                'attempt_no' => 'required',
            ]);
            if ($validator->fails())
                return $this->errorWithData($validator->errors()->first(), $validator->errors());

            $data = (new Assessment())->assessmentQuestionDetail($student_id, $request->test_code, $request->attempt_no)
                ->groupBy('mistakes_category')->transform(function ($item, $k) {
                    return $item->groupBy('mistakes_type');
                });
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function assessmentPostDetails(Request $request)
    {
        $date = Carbon::now()->startOfDay()->toDateTimeString();
        $assessment_detail=collect(Assessment::where('regnnumb', $request->student_id)->where('testdate', $date)->first()->toArray()) ?? null;
        $activityLog = ActivityLog::updateOrCreate(
            ['id' => 0],
            [
                'student_id' => $request->student_id,
                'sb_id' => auth()->user()->sb_id,
                'request_data' => collect($request->all()),
                'assessment_detail' => $assessment_detail,
            ]
        );
        $activity_log_id = $activityLog->id;
        try {
            $rules = [
                'student_id' => 'required',
                'start_date_time' => 'required|date_format:Y-m-d H:i:s',
                'end_date_time' => 'required|date_format:Y-m-d H:i:s',
//                'result' => 'required|array',
            ];
            if ($request->mistake) {
                $rules['mistake'] = 'array';
            }

            Log::channel('api')->info(json_encode($request->all()));

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $activityLog->update([
                    'student_id' => $request->student_id,
                    'action' => 'invalid',
                    'response_data' => $validator->errors(),
                ]);
                return $this->errorWithData($validator->errors()->first(), $validator->errors());
            }

            $test_date = $request->start_date_time . '>' . $request->end_date_time;
            $result = '';
            $mistake = '';
            $action = '';
            if (is_array($request->result)) {
                foreach ($request->result as $code => $flag) {
                    $result .= $code . '>' . $flag . ',';
                    $action = $flag;
                }
                if (strlen($result) > 0)
                    $result = substr_replace($result, "", -1);
            }

            if ($request->mistake && is_array($request->mistake)) {
                foreach ($request->mistake as $mistake_code => $mistake_count) {
                    $mistake .= $mistake_code . ">" . $mistake_count . ",";
                }
                if (strlen($mistake) > 0)
                    $mistake = substr_replace($mistake, "", -1);
            }

            $data = (new Assessment())->assessmentPostDetail((string)$request->student_id, $test_date, $result, $mistake, $request->remarks);
            $activityLog->update([
                'student_id' => $request->student_id,
                'action' => $action == "A" ? "absent" : "result submit",
                'response_data' => $data,
            ]);
            if ($data->get('status') == '0') {
                $assessment_report = new AssessmentReport();
                $assessment_report->student_id = $request->student_id;
                $assessment_report->start_time = $request->start_date_time;
                $assessment_report->end_time = $request->end_date_time;
                $assessment_report->result = $action == "A" ? 'Absent' : ($data->get('message') == "Student Passed The Test And Saved Successfully!!!" ? 'Pass' : "Fail");
                $assessment_report->remarks = $request->remarks;
                $assessment_report->assessor_id = auth()->user()->id;
                $assessment_report->activity_log_id = $activity_log_id;
                $assessment_report->save();
                return $this->success([], $data->get('message'));
            }

            return $this->error($data->get('message'));
        } catch (\Exception $e) {
            $activityLog->update([
                'action' => 'exception',
                'response_data' => $e->getMessage(),
            ]);
            return $this->error($e->getMessage(), true);
        }
    }

    public function activityLog(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required',
                'action' => ['required', Rule::in(['start', 'resume', 'discard'])],
            ]);
            $activityLog = ActivityLog::updateOrCreate(
                ['id' => 0],
                [
                    'student_id' => $request->student_id,
                    'sb_id' => auth()->user()->sb_id,
                    'request_data' => collect($request->all()),
                ]
            );

            if ($validator->fails()) {
                $activityLog = $activityLog->update([
                    'student_id' => $request->student_id,
                    'action' => 'invalid',
                    'response_data' => $validator->errors(),
                ]);
                return $this->errorWithData($validator->errors()->first(), $validator->errors());
            }

            $activityLog = $activityLog->update([
                'student_id' => $request->student_id,
                'action' => $request->action,
                'response_data' => collect(['success' => true]),
            ]);

            return $this->success([]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

}
