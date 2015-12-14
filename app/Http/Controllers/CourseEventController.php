<?php

namespace StudentInfo\Http\Controllers;


use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use StudentInfo\ErrorCodes\UserErrorCodes;
use StudentInfo\Http\Requests\Create\CreateCourseEventRequest;
use StudentInfo\Http\Requests\Update\UpdateCourseEventRequest;
use StudentInfo\Models\Course;
use StudentInfo\Models\CourseEvent;
use StudentInfo\Repositories\ClassroomRepositoryInterface;

class CourseEventController extends EventController
{
    public function createEvent(CreateCourseEventRequest $request, ClassroomRepositoryInterface $classroomRepository)
    {
        $event    = new CourseEvent(new ArrayCollection());
        $startsAt = Carbon::createFromFormat('Y-m-d H:i', $request->get('startsAt'));
        $endsAt   = Carbon::createFromFormat('Y-m-d H:i', $request->get('endsAt'));
        if ($endsAt->lte($startsAt)) {
            return $this->returnError(500, UserErrorCodes::INCORRECT_TIME);
        }

        $course = $this->courseRepository->find($request['courseId']);
        if ($course === null) {
            return $this->returnError(500, UserErrorCodes::COURSE_NOT_IN_DB);
        }

        $classroomsEntry = $request->get('classrooms');
        $classrooms      = [];

        for ($i = 0; $i < count($classroomsEntry); $i++) {
            $classroom = $classroomRepository->find($classroomsEntry[$i]);
            if ($classroom === null) {
                continue;
            }
            $classrooms[] = $classroom;
        }

        $event->setType($request->get('type'));
        $event->setDescription($request->get('description'));
        $event->setCourse($course);
        $event->setClassrooms($classrooms);
        $event->setStartsAt($startsAt);
        $event->setEndsAt($endsAt);

        $this->eventRepository->create($event);
        return $this->returnSuccess([
            'successful' => $event,
        ]);
    }

    public function updateEvent(UpdateCourseEventRequest $request, ClassroomRepositoryInterface $classroomRepository, $id)
    {
        if ($this->eventRepository->find($id) === null) {
            return $this->returnError(500, UserErrorCodes::EVENT_NOT_IN_DB);
        }

        /** @var  CourseEvent $event */
        $event = $this->eventRepository->find($id);

        $startsAt = Carbon::createFromFormat('Y-m-d H:i', $request['startsAt']);
        $endsAt   = Carbon::createFromFormat('Y-m-d H:i', $request['endsAt']);
        if ($endsAt->lte($startsAt)) {
            return $this->returnError(500, UserErrorCodes::INCORRECT_TIME);
        }
        /** @var Course $course */
        $course = $this->courseRepository->find($request['courseId']);

        if ($course === null) {
            return $this->returnError(500, UserErrorCodes::COURSE_NOT_IN_DB);
        }

        $classroomsEntry = $request->get('classrooms');
        $classrooms      = [];

        for ($i = 0; $i < count($classroomsEntry); $i++) {
            $classroom = $classroomRepository->find($classroomsEntry[$i]);
            if ($classroom === null) {
                continue;
            }
            $classrooms[] = $classroom;
        }

        $event->setType($request['type']);
        $event->setDescription($request['description']);
        $event->setCourse($course);
        $event->setClassrooms($classrooms);
        $event->setStartsAt($startsAt);
        $event->setEndsAt($endsAt);

        $this->eventRepository->update($event);


        return $this->returnSuccess([
            'event' => $event,
        ]);
    }
}