<?php


namespace StudentInfo\Http\Controllers;


use Illuminate\Contracts\Auth\Guard;
use StudentInfo\ErrorCodes\UserErrorCodes;
use StudentInfo\Http\Requests\AddLectureRequest;
use StudentInfo\Http\Requests\DeleteLectureRequest;
use StudentInfo\Models\Classroom;
use StudentInfo\Models\Course;
use StudentInfo\Models\Lecture;
use StudentInfo\Models\Professor;
use StudentInfo\Repositories\ClassroomRepositoryInterface;
use StudentInfo\Repositories\CourseRepositoryInterface;
use StudentInfo\Repositories\FacultyRepositoryInterface;
use StudentInfo\Repositories\LectureRepositoryInterface;
use StudentInfo\Repositories\ProfessorRepositoryInterface;

class LectureController extends ApiController
{
    /**
     * @var LectureRepositoryInterface
     */
    protected $lectureRepository;

    /**
     * @var Guard
     */
    protected $guard;
    /**
     * @var FacultyRepositoryInterface
     */
    protected $professorRepository;
    /**
     * @var CourseRepositoryInterface
     */
    protected $courseRepository;
    /**
     * @var ClassroomRepositoryInterface
     */
    protected $classroomRepository;

    /**
     * LectureController constructor.
     * @param LectureRepositoryInterface   $lectureRepository
     * @param Guard                        $guard
     * @param ProfessorRepositoryInterface $professorRepository
     * @param CourseRepositoryInterface    $courseRepository
     * @param ClassroomRepositoryInterface $classroomRepository
     */
    public function __construct(LectureRepositoryInterface $lectureRepository, Guard $guard, ProfessorRepositoryInterface $professorRepository, CourseRepositoryInterface $courseRepository, ClassroomRepositoryInterface $classroomRepository)
    {
        $this->lectureRepository   = $lectureRepository;
        $this->guard               = $guard;
        $this->professorRepository = $professorRepository;
        $this->courseRepository    = $courseRepository;
        $this->classroomRepository = $classroomRepository;
    }


    public function addLecture(AddLectureRequest $request)
    {
        $professorId = $request->get('professorId');
        $courseId    = $request->get('courseId');
        $classroomId = $request->get('classroomId');

        /** @var Professor $professor */
        $professor = $this->professorRepository->find($professorId);

        /** @var Course $course */
        $course = $this->courseRepository->find($courseId);

        /** @var Classroom $classroom */
        $classroom = $this->classroomRepository->find($classroomId);

        $lecture = new Lecture();

        if ($professor == null) {
            return $this->returnError(500, UserErrorCodes::PROFESSOR_NOT_IN_DB);
        }
        if ($course == null) {
            return $this->returnError(500, UserErrorCodes::COURSE_NOT_IN_DB);
        }
        if ($classroom == null) {
            return $this->returnError(500, UserErrorCodes::CLASSROOM_NOT_IN_DB);
        }

        $professor->setLectures([$lecture]);
        $lecture->setProfessor($professor);
        $course->setLectures([$lecture]);
        $lecture->setCourse($course);
        $lecture->setClassroom($classroom);

        $this->lectureRepository->create($lecture);
        return $this->returnSuccess([
            'lecture' => $lecture,
        ]);

    }

    public function deleteLectures(DeleteLectureRequest $request)
    {
        $ids = $request->get('ids');
        foreach ($ids as $id) {
            $lecture = $this->lectureRepository->find($id);
            if ($lecture === null) {
                continue;
            }
            $this->lectureRepository->destroy($lecture);
        }
        return $this->returnSuccess();
    }
}