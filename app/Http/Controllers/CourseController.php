<?php


namespace StudentInfo\Http\Controllers;

use LucaDegasperi\OAuth2Server\Authorizer;
use StudentInfo\ErrorCodes\CourseErrorCodes;
use StudentInfo\Http\Requests\AddFromCSVRequest;
use StudentInfo\Http\Requests\Create\CreateCourseRequest;
use StudentInfo\Http\Requests\StandardRequest;
use StudentInfo\Http\Requests\Update\UpdateCourseRequest;
use StudentInfo\Models\Course;
use StudentInfo\Models\User;
use StudentInfo\Repositories\CourseRepositoryInterface;
use StudentInfo\Repositories\FacultyRepositoryInterface;
use StudentInfo\Repositories\UserRepositoryInterface;

class CourseController extends ApiController
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var CourseRepositoryInterface $courseRepository
     */
    protected $courseRepository;

    /**
     * @var FacultyRepositoryInterface $facultyRepository
     */
    protected $facultyRepository;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepositoryInterface;

    /**
     * @var Authorizer
     */
    protected $authorizer;

    /**
     * CourseController constructor.
     * @param UserRepositoryInterface           $userRepository
     * @param CourseRepositoryInterface  $courseRepository
     * @param FacultyRepositoryInterface $facultyRepository
     * @param UserRepositoryInterface           $userRepositoryInterface
     * @param Authorizer                        $authorizer
     */
    public function __construct(UserRepositoryInterface $userRepository, CourseRepositoryInterface $courseRepository, FacultyRepositoryInterface $facultyRepository, UserRepositoryInterface $userRepositoryInterface, Authorizer $authorizer)
    {
        $this->userRepository   = $userRepository;
        $this->courseRepository = $courseRepository;
        $this->facultyRepository   = $facultyRepository;
        $this->userRepositoryInterface = $userRepositoryInterface;
        $this->authorizer       = $authorizer;
    }

    public function createCourse(CreateCourseRequest $request, $faculty)
    {
        $code = $request->get('code');
        $course = new Course();
        $course->setCode($code);
        $course->setEspb($request->get('espb'));
        $course->setSemester($request->get('semester'));
        $course->setName($request->get('name'));
        $course->setOrganisation($this->userRepository->find($this->authorizer->getResourceOwnerId())->getOrganisation());

        $this->courseRepository->create($course);

        return $this->returnSuccess([
            'course' => $course,
        ]);
    }

    public function retrieveCourse(StandardRequest $request, $faculty, $id)
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($id);

        if ($course === null) {
            return $this->returnError(500, CourseErrorCodes::COURSE_NOT_IN_DB);
        }

        if ($course->getOrganisation()->getSlug() != $faculty) {
            return $this->returnError(500, CourseErrorCodes::COURSE_DOES_NOT_BELONG_TO_THIS_FACULTY);
        }

        $semester = $request->get('semester', 'current');
        $year     = $request->get('year', 'current');

        if (($semester == 'current') || ($year == 'current')) {
            $userId = $this->authorizer->getResourceOwnerId();
            /** @var User $user */
            $user = $this->userRepositoryInterface->find($userId);
            if ($semester == 'current') {
                $semester = $user->getOrganisation()->getSettings()->getSemester();
            }
            if ($year == 'current') {
                $year = $user->getOrganisation()->getSettings()->getYear();
            }
        } else {
            $semester = (int)$request->get('semester');
            $year     = (int)$request->get('year');
        }

        $lectures = [];
        foreach ($course->getLectures() as $lecture) {
            if (($lecture->getCourse()->getSemester() % 2 === $semester % 2) && ($lecture->getYear() === $year)) {
                $lectures[] = $lecture;
            }
        }
        $course->setLectures($lectures);

        return $this->returnSuccess([
            'course' => $course,
        ]);
    }

    public function retrieveCourses(StandardRequest $request, $faculty, $start = 0, $count = 2000)
    {
        /** @var Course[] $courses */
        $courses = $this->courseRepository->all($faculty, $start, $count);

        $semester = $request->get('semester', 'current');
        $year     = $request->get('year', 'current');

        if (($semester == 'current') || ($year == 'current')) {
            $userId = $this->authorizer->getResourceOwnerId();
            /** @var User $user */
            $user = $this->userRepositoryInterface->find($userId);
            if ($semester == 'current') {
                $semester = $user->getOrganisation()->getSettings()->getSemester();
            }
            if ($year == 'current') {
                $year = $user->getOrganisation()->getSettings()->getYear();
            }
        } else {
            $semester = (int)$request->get('semester');
            $year     = (int)$request->get('year');
        }

        foreach ($courses as $course) {
            $lectures = [];
            foreach ($course->getLectures() as $lecture) {
                if (($lecture->getCourse()->getSemester() % 2 === $semester % 2) && ($lecture->getYear() === $year)) {
                    $lectures[] = $lecture;
                }
            }
            $course->setLectures($lectures);
        }
        return $this->returnSuccess($courses);
    }

    public function updateCourse(UpdateCourseRequest $request, $faculty, $id)
    {
        if ($this->courseRepository->find($id) === null) {
            return $this->returnError(500, CourseErrorCodes::COURSE_NOT_IN_DB);
        }

        /** @var  Course $course */
        $course = $this->courseRepository->find($id);

        $course->setCode($request->get('code'));
        $course->setEspb($request->get('espb'));
        $course->setSemester($request->get('semester'));
        $course->setName($request->get('name'));
        $course->setOrganisation($this->userRepository->find($this->authorizer->getResourceOwnerId())->getOrganisation());

        $this->courseRepository->update($course);


        return $this->returnSuccess([
            'course' => $course,
        ]);
    }

    public function deleteCourse($faculty, $id)
    {
        $course = $this->courseRepository->find($id);
        if ($course === null) {
            return $this->returnError(500, CourseErrorCodes::COURSE_NOT_IN_DB);
        }
        $this->courseRepository->destroy($course);

        return $this->returnSuccess();
    }

    public function addCoursesFromCSV(AddFromCSVRequest $request, $faculty)
    {
        $addedCourses = [];

        $handle = $request->file('import');

        $file_path = $handle->getPathname();
        $resource  = fopen($file_path, "r");

        $organisation = $this->userRepository->find($this->authorizer->getResourceOwnerId())->getOrganisation();

        while (($data = fgetcsv($resource, 1000, ",")) !== FALSE) {
            $name     = $data[0];
            $code     = $data[1];
            $espb     = $data[2];
            $semester = $data[3];

            $course = new Course();
            $course->setName($name);
            $course->setCode($code);
            $course->setEspb($espb);
            $course->setSemester($semester);
            $course->setOrganisation($organisation);

            $this->courseRepository->create($course);

            $addedCourses[] = $course;
        }

        return $this->returnSuccess();
    }
}