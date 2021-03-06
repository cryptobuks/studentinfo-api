<?php

namespace StudentInfo\Http\Controllers;

use Exception;
use StudentInfo\ErrorCodes\FacultyErrorCodes;
use StudentInfo\ErrorCodes\UserErrorCodes;
use StudentInfo\Http\Requests\Create\CreateFacultyRequest;
use StudentInfo\Http\Requests\Update\UpdateFacultyRequest;
use StudentInfo\Models\Faculty;
use StudentInfo\Repositories\FacultyRepositoryInterface;
use StudentInfo\ValueObjects\Settings;

class FacultyController extends ApiController
{
    /**
     * @var FacultyRepositoryInterface
     */
    protected $facultyRepository;

    /**
     * FacultyController constructor.
     * @param FacultyRepositoryInterface $facultyRepository
     */
    public function __construct(FacultyRepositoryInterface $facultyRepository)
    {
        $this->facultyRepository = $facultyRepository;
    }

    public function createFaculty(CreateFacultyRequest $request)
    {
        $name = $request->get('name');
        if ($this->facultyRepository->findFacultyByName($name)) {
            return $this->returnError(500, FacultyErrorCodes::FACULTY_ALREADY_EXISTS);
        }
        $settings = new Settings();
        $settings->setWallpaperPath('');
        $settings->setYear($request->get('year'));
        $settings->setSemester($request->get('semester'));
        $settings->setLanguage($request->get('language'));

        $faculty = new Faculty();
        $faculty->setName($name);
        $faculty->setSlug($request->get('slug'));
        $faculty->setUniversity($request->get('university'));
        $faculty->setSettings($settings);

        $this->facultyRepository->create($faculty);

        return $this->returnSuccess([
            'faculty' => $faculty,
        ]);
    }

    public function retrieveFaculty($id)
    {
        $faculty = $this->facultyRepository->find($id);

        if ($faculty === null) {
            return $this->returnError(500, FacultyErrorCodes::FACULTY_NOT_IN_DB);
        }

        return $this->returnSuccess([
            'faculty' => $faculty,
        ]);
    }

    public function retrieveFaculties($start = 0, $count = 2000)
    {
        $faculty = $this->facultyRepository->all(null, $start, $count);

        return $this->returnSuccess($faculty);
    }

    public function updateFaculty(UpdateFacultyRequest $request, $id)
    {
        if ($this->facultyRepository->find($id) === null) {
            return $this->returnError(500, FacultyErrorCodes::FACULTY_NOT_IN_DB);
        }

        $settings = new Settings();
        $settings->setWallpaperPath('');
        $settings->setYear($request->get('year'));
        $settings->setSemester($request->get('semester'));
        $settings->setLanguage($request->get('language'));

        /** @var  Faculty $faculty */
        $faculty = $this->facultyRepository->find($id);

        $faculty->setName($request->get('name'));
        $faculty->setUniversity($request->get('university'));

        $this->facultyRepository->update($faculty);


        return $this->returnSuccess([
            'faculty' => $faculty,
        ]);
    }

    public function deleteFaculty($id)
    {
        $faculty = $this->facultyRepository->find($id);
        if ($faculty === null) {
            return $this->returnError(500, FacultyErrorCodes::FACULTY_NOT_IN_DB);
        }
        try {
            $this->facultyRepository->destroy($faculty);
        } catch (Exception $e) {
            return $this->returnError(500, UserErrorCodes::USER_BELONGS_TO_THIS_FACULTY);
        }

        return $this->returnSuccess();
    }
}