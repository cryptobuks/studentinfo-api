<?php

use StudentInfo\Repositories\FacultyRepositoryInterface;
use StudentInfo\Repositories\SuperUserRepositoryInterface;
use StudentInfo\Repositories\UserRepositoryInterface;

Route::get('/', function () {
    return view('welcome');
});

/* Test Routes */

Route::post('importStudents', 'StudentController@addStudentsFromCSV');

Route::post('importClassrooms', 'ClassroomController@addClassroomsFromCSV');

Route::post('importCourses', 'CourseController@addCoursesFromCSV');

Route::post('importProfessors', 'ProfessorController@addProfessorsFromCSV');

Route::post('wallpaper', 'SettingsController@setWallpaper');

Route::post('language', 'SettingsController@setLanguage');

Route::get('/addSuperUser', function (SuperUserRepositoryInterface $superUserRepository, FacultyRepositoryInterface $facultyRepository) {
    $superUser = new \StudentInfo\Models\SuperUser();
    $superUser->setFirstName("Nebojsa");
    $superUser->setLastName("Urosevic");
    $superUser->setEmail(new \StudentInfo\ValueObjects\Email("nu1@gmail.com"));
    $superUser->setPassword(new \StudentInfo\ValueObjects\Password("blabla"));
    $superUser->setOrganisation($facultyRepository->findFacultyByName('Racunarski fakultet'));
    $superUser->setRememberToken("bla");

    $superUserRepository->create($superUser);
});

Route::get('/addAdmin', function (FacultyRepositoryInterface $facultyRepository, UserRepositoryInterface $userRepository) {
    $admin = new \StudentInfo\Models\Admin();
    $admin->setFirstName("Nebojsa");
    $admin->setLastName("Urosevic");
    $admin->setEmail(new \StudentInfo\ValueObjects\Email("nu@gmail.com"));
    $admin->setPassword(new \StudentInfo\ValueObjects\Password("blabla"));
    $admin->setRememberToken("bla");
    $admin->setOrganisation($facultyRepository->findFacultyByName('Racunarski fakultet'));

    $userRepository->create($admin);
});

Route::get('/addStudent', function (FacultyRepositoryInterface $facultyRepository, UserRepositoryInterface $userRepository) {
    $student = new \StudentInfo\Models\Student();
    $student->setFirstName('Milan');
    $student->setLastName('Vucic');
    $student->setEmail(new \StudentInfo\ValueObjects\Email('mv@gmail1.com'));
    $student->setPassword(new \StudentInfo\ValueObjects\Password('blabla'));
    $student->setIndexNumber('1244221');
    $student->setRememberToken('bla');
    $student->generateRegisterToken();
    $student->setYear(3);
    $student->setOrganisation($facultyRepository->findFacultyByName('Racunarski fakultet'));

    $userRepository->create($student);
});

Route::get('/addFaculty', function (FacultyRepositoryInterface $repository) {
    $faculty = new \StudentInfo\Models\Faculty();
    $faculty->setName('Racunarski fakultet');
    $faculty->setUniversity('Union');
    $settings = new \StudentInfo\ValueObjects\Settings();
    $settings->setLanguage('english');
    $settings->setWallpaperPath('/settings/default/wallpaper/wallpaper.png');
    $faculty->setSettings($settings);

    $repository->create($faculty);
});
Route::get('data', 'DataController@getData');

Route::get('user/{user_id}', 'UserController@getProfile');

Route::put('user/{user_id}', 'UserController@updateProfile');

Route::post('auth', 'AuthController@login');

Route::delete('auth', 'AuthController@logout');

Route::post('register', 'RegisterController@issueRegisterTokens');

Route::get('updateRegisterToken/{id}', ['middleware' => 'role:token.update', 'uses' => 'RegisterController@updateRegisterToken']);

Route::get('register/{registerToken}', 'RegisterController@registerStudent');

Route::post('register/{registerToken}', 'RegisterController@createPassword');

Route::post('chooseLectures', 'StudentController@chooseLectures');

Route::get('showMyLectures', 'StudentController@showMyLectures');

Route::post('addClassroom/{eventId}', 'EventController@AddClassroom');

Route::get('years', 'GroupController@getAllYears');

Route::get('getClassrooms/{eventId}', 'EventController@getClassrooms');

Route::delete('deleteClassroom/{eventId}', 'EventController@deleteClassroom');

Route::post('student', 'StudentController@createStudent');

Route::post('classroom', 'ClassroomController@createClassroom');

Route::post('courseEvent', 'CourseEventController@createEvent');

Route::post('groupEvent', 'GroupEventController@createEvent');

Route::post('globalEvent', 'GlobalEventController@createEvent');

Route::post('professor', 'ProfessorController@createProfessor');

Route::post('assistant', 'AssistantController@createAssistant');

Route::post('lecture', 'LectureController@createLecture');

Route::post('course', 'CourseController@createCourse');

Route::post('group', 'GroupController@createGroup');

Route::post('admin', 'AdminController@createAdmin');

Route::post('lectureNotification', 'LectureNotificationController@createNotification');

Route::post('eventNotification', 'EventNotificationController@createNotification');

Route::post('faculty', 'FacultyController@createFaculty');

Route::get('student/{id}', ['middleware' => 'role:student.retrieve', 'uses' => 'StudentController@retrieveStudent']);

Route::get('students/{start?}/{count?}', ['middleware' => 'role:student.retrieve', 'uses' => 'StudentController@retrieveStudents']);

Route::get('classroom/{id}', 'ClassroomController@retrieveClassroom');

Route::get('classrooms/{start?}/{count?}', 'ClassroomController@retrieveClassrooms');

Route::get('professor/{id}', 'ProfessorController@retrieveProfessor');

Route::get('professors/{start?}/{count?}', 'ProfessorController@retrieveProfessors');

Route::get('assistant/{id}', 'AssistantController@retrieveAssistant');

Route::get('assistants/{start?}/{count?}', 'AssistantController@retrieveAssistants');

Route::get('groupEvents/{groupId}', 'GroupEventController@retrieveGroupEvents');

Route::get('event/{id}', ['middleware' => 'role:event.retrieve', 'uses' => 'EventController@retrieveEvent']);

Route::get('events/{start?}/{count?}', ['middleware' => 'role:event.retrieve', 'uses' => 'EventController@retrieveEvents']);

Route::get('lecture/{id}', ['middleware' => 'role:lecture.retrieve', 'uses' => 'LectureController@retrieveLecture']);

Route::get('lectures/{start?}/{count?}', ['middleware' => 'role:lecture.retrieve', 'uses' => 'LectureController@retrieveLectures']);

Route::get('eventNotifications/{id}', 'EventNotificationController@retrieveNotificationsForEvent');

Route::get('eventNotification/{id}', 'EventNotificationController@retrieveNotification');

Route::get('eventNotifications/{start?}/{count?}', 'EventNotificationController@retrieveNotifications');

Route::get('lectureNotifications/{id}', 'LectureNotificationController@retrieveNotificationsForLecture');

Route::get('lectureNotification/{id}', 'LectureNotificationController@retrieveNotification');

Route::get('lectureNotifications/{start?}/{count?}', 'LectureNotificationController@retrieveNotifications');

Route::get('course/{id}', ['middleware' => 'role:course.retrieve', 'uses' => 'CourseController@retrieveCourse']);

Route::get('courses/{start?}/{count?}', ['middleware' => 'role:course.retrieve', 'uses' => 'CourseController@retrieveCourses']);

Route::get('group/{id}', 'GroupController@retrieveGroup');

Route::get('groups/{start?}/{count?}', 'GroupController@retrieveGroups');

Route::get('admin/{id}', ['middleware' => 'role:admin.retrieve', 'uses' => 'AdminController@retrieveAdmin']);

Route::get('admins/{start?}/{count?}', ['middleware' => 'role:admin.retrieve', 'uses' => 'AdminController@retrieveAdmins']);

Route::get('faculty/{id}', ['middleware' => 'role:faculty.retrieve', 'uses' => 'FacultyController@retrieveFaculty']);

Route::get('faculties/{start?}/{count?}', ['middleware' => 'role:faculty.retrieve', 'uses' => 'FacultyController@retrieveFaculties']);

Route::put('student/{id}', ['middleware' => 'role:student.update', 'uses' => 'StudentController@updateStudent']);

Route::put('classroom/{id}', ['middleware' => 'role:classroom.update', 'uses' => 'ClassroomController@updateClassroom']);

Route::put('professor/{id}', ['middleware' => 'role:teacher.update', 'uses' => 'ProfessorController@updateProfessor']);

Route::put('assistant/{id}', ['middleware' => 'role:teacher.update', 'uses' => 'AssistantController@updateAssistant']);

Route::put('course/{id}', ['middleware' => 'role:course.update', 'uses' => 'CourseController@updateCourse']);

Route::put('lecture/{id}', ['middleware' => 'role:lecture.update', 'uses' => 'LectureController@updateLecture']);

Route::put('group/{id}', ['middleware' => 'role:group.update', 'uses' => 'GroupController@updateGroup']);

Route::put('courseEvent/{id}', ['middleware' => 'role:event.update', 'uses' => 'CourseEventController@updateEvent']);

Route::put('groupEvent/{id}', ['middleware' => 'role:event.update', 'uses' => 'GroupEventController@updateEvent']);

Route::put('globalEvent/{id}', ['middleware' => 'role:event.update', 'uses' => 'GlobalEventController@updateEvent']);

Route::put('admin/{id}', ['middleware' => 'role:admin.update', 'uses' => 'AdminController@updateAdmin']);

Route::put('faculty/{id}', ['middleware' => 'role:faculty.update', 'uses' => 'FacultyController@updateFaculty']);

Route::put('eventNotification/{id}', ['middleware' => 'role:notification.edit', 'uses' => 'EventNotificationController@updateNotification']);

Route::put('lectureNotification/{id}', ['middleware' => 'role:notification.edit', 'uses' => 'LectureNotificationController@updateNotification']);

Route::delete('classroom/{id}' , ['middleware' => 'role:classroom.delete', 'uses' => 'ClassroomController@deleteClassroom']);

Route::delete('course/{id}' , ['middleware' => 'role:course.delete', 'uses' => 'CourseController@deleteCourse']);

Route::delete('professor/{id}', ['middleware' => 'role:teacher.delete', 'uses' => 'ProfessorController@deleteProfessor']);

Route::delete('assistant/{id}', ['middleware' => 'role:teacher.delete', 'uses' => 'AssistantController@deleteAssistant']);

Route::delete('lecture/{id}' , ['middleware' => 'role:lecture.delete', 'uses' => 'LectureController@deleteLecture']);

Route::delete('group/{id}' , ['middleware' => 'role:group.delete', 'uses' => 'GroupController@deleteGroup']);

Route::delete('event/{id}', ['middleware' => 'role:event.delete', 'uses' => 'EventController@deleteEvent']);

Route::delete('student/{id}' , ['middleware' => 'role:student.delete', 'uses' => 'StudentController@deleteStudent']);

Route::delete('admin/{id}' , ['middleware' => 'role:admin.delete', 'uses' => 'AdminController@deleteAdmin']);

Route::delete('faculty/{id}' , ['middleware' => 'role:faculty.delete', 'uses' => 'FacultyController@deleteFaculty']);

Route::delete('notification/{id}' , ['middleware' => 'role:notification.delete', 'uses' => 'NotificationController@deleteNotification']);

Route::get('notifications/between/{start}/{end}', ['middleware' => 'role:notification.retrieve', 'uses' => 'NotificationController@getNotificationsInInterval']);

