<?php

namespace StudentInfo\Http\Controllers;

use LucaDegasperi\OAuth2Server\Authorizer;
use StudentInfo\ErrorCodes\TeacherErrorCodes;
use StudentInfo\Repositories\TeacherRepositoryInterface;
use StudentInfo\Repositories\UserRepositoryInterface;

class TeacherController extends ApiController
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;
    /**
     * @var TeacherRepositoryInterface
     */
    protected $teacherRepository;

    /**
     * @var Authorizer
     */
    protected $authorizer;

    /**
     * StudentController constructor.
     * @param UserRepositoryInterface    $userRepository
     * @param TeacherRepositoryInterface $teacherRepository
     * @param Authorizer                 $authorizer
     */
    public function __construct(UserRepositoryInterface $userRepository, TeacherRepositoryInterface $teacherRepository, Authorizer $authorizer)
    {
        $this->userRepository = $userRepository;
        $this->teacherRepository = $teacherRepository;
        $this->authorizer     = $authorizer;
    }

    public function retrieveTeacher($faculty, $id)
    {
        $teacher = $this->teacherRepository->find($id);

        if ($teacher === null) {
            return $this->returnError(500, TeacherErrorCodes::TEACHER_NOT_IN_DB);
        }

        if ($teacher->getOrganisation()->getSlug() != $faculty) {
            return $this->returnError(500, TeacherErrorCodes::TEACHER_DOES_NOT_BELONG_TO_THIS_FACULTY);
        }

        return $this->returnSuccess([
            'teacher' => $teacher,
        ]);
    }

    public function retrieveTeachers($faculty, $start = 0, $count = 2000)
    {
        $teachers = $this->teacherRepository->all($faculty, $start, $count);

        return $this->returnSuccess($teachers);
    }
}