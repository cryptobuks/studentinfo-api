<?php

namespace StudentInfo\Http\Controllers;

use DateTime as DateTimeDateTime;
use Doctrine\Common\Collections\ArrayCollection;
use StudentInfo\ErrorCodes\EventErrorCodes;
use StudentInfo\ErrorCodes\GroupErrorCodes;
use StudentInfo\ErrorCodes\UserErrorCodes;
use StudentInfo\Http\Requests\Create\CreateGroupEventRequest;
use StudentInfo\Http\Requests\Update\UpdateGroupEventRequest;
use StudentInfo\Models\Group;
use StudentInfo\Models\GroupEvent;
use StudentInfo\ValueObjects\Datetime;

class GroupEventController extends EventController
{
    public function createEvent(CreateGroupEventRequest $request, $faculty)
    {
        $event = new GroupEvent(new ArrayCollection());
        $startsAt = DateTimeDateTime::createFromFormat('Y-m-d H:i', $request->get('startsAt'));
        $endsAt = DateTimeDateTime::createFromFormat('Y-m-d H:i', $request->get('endsAt'));
        if ($endsAt < $startsAt) {
            return $this->returnError(500, UserErrorCodes::INCORRECT_TIME);
        }

        /** @var Group $group */
        $group = $this->groupRepository->find($request['groupId']);
        if ($group === null) {
            return $this->returnError(500, GroupErrorCodes::GROUP_NOT_IN_DB);
        }

        $classroomsEntry = $request->get('classrooms');
        $classrooms      = [];

        for ($i = 0; $i < count($classroomsEntry); $i++) {
            $classroom = $this->classroomRepository->find($classroomsEntry[$i]);
            if ($classroom === null) {
                continue;
            }
            $classrooms[] = $classroom;
        }
        $datetime = new Datetime();
        $datetime->setStartsAt($startsAt);
        $datetime->setEndsAt($endsAt);

        $event->setType($request->get('type'));
        $event->setDescription($request->get('description'));
        $event->setGroup($group);
        $event->setClassrooms($classrooms);
        $event->setDatetime($datetime);
        $event->setOrganisation($this->userRepository->find($this->authorizer->getResourceOwnerId())->getOrganisation());

        $this->eventRepository->create($event);
        return $this->returnSuccess([
            'successful' => $event,
        ]);
    }

    public function retrieveEvent($faculty, $id)
    {
        /** @var GroupEvent $event */
        $event = $this->groupEventRepository->find($id);

        if ($event === null) {
            return $this->returnError(500, EventErrorCodes::EVENT_NOT_IN_DB);
        }

        if ($event->getOrganisation()->getSlug() != $faculty) {
            return $this->returnError(500, EventErrorCodes::EVENT_DOES_NOT_BELONG_TO_THIS_FACULTY);
        }

        return $this->returnSuccess([
            'event' => $event,
        ]);
    }

    public function retrieveEvents($faculty, $start = 0, $count = 2000)
    {
        $events = $this->groupEventRepository->all($faculty, $start, $count);

        return $this->returnSuccess($events);
    }

    public function updateEvent(UpdateGroupEventRequest $request, $faculty, $id)
    {
        if ($this->eventRepository->find($id) === null) {
            return $this->returnError(500, EventErrorCodes::EVENT_NOT_IN_DB);
        }

        /** @var  GroupEvent $event */
        $event = $this->eventRepository->find($id);

        $startsAt = DateTimeDateTime::createFromFormat('Y-m-d H:i', $request->get('startsAt'));
        $endsAt   = DateTimeDateTime::createFromFormat('Y-m-d H:i', $request->get('endsAt'));
        if ($endsAt < $startsAt) {
            return $this->returnError(500, UserErrorCodes::INCORRECT_TIME);
        }

        $classroomsEntry = $request->get('classrooms');
        $classrooms      = [];

        for ($i = 0; $i < count($classroomsEntry); $i++) {
            $classroom = $this->classroomRepository->find($classroomsEntry[$i]);
            if ($classroom === null) {
                continue;
            }
            $classrooms[] = $classroom;
        }
        $datetime = new Datetime();
        $datetime->setStartsAt($startsAt);
        $datetime->setEndsAt($endsAt);

        $event->setType($request['type']);
        $event->setDescription($request['description']);
        $event->setClassrooms($classrooms);
        $event->setDatetime($datetime);
        $event->setOrganisation($this->userRepository->find($this->authorizer->getResourceOwnerId())->getOrganisation());

        $this->eventRepository->update($event);


        return $this->returnSuccess([
            'event' => $event
        ]);
    }
}