<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('login/{service}', 'AuthController@handleProviderToken');

Route::get('/test','TeacherController@index');
Route::get('/testCreate','TeacherController@create');
Route::get('/testDestroy','TeacherController@destroy');
Route::get('/lessons/{class_id}', 'Cerevids\CourseController@lessonByClass')->name('coursesByClass');
Route::get('/cereouts/leaderboard/toptryout/{id}', 'Cereouts\LeaderboardController@getTopTryout');
Route::get('courses/', 'Cerevids\CourseController@index')->name('courses');

Route::post('/notification/handler', 'Payment\PaymentController@notificationHandler')->name('notification.handler');

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    //change password
    Route::post('user/changePassword/{id}', 'AuthController@changePassword');
    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        //get profile
        Route::get('user', 'AuthController@user');
        //change profile
        Route::put('user/{id}', 'AuthController@changeProfile');
        //change avatar
        Route::post('changePhotoProfile/{id}', 'AuthController@changePhotoProfile');
        //get avatar
        Route::get('photoProfile/{id}', 'AuthController@getPhotoProfile');
    });
});

Route::group([    
    'middleware' => 'api',    
    'prefix' => 'password'
], function () {    
	//create token 
    Route::post('create', 'PasswordResetController@create');
    //find token 
    Route::get('find/{token}', 'PasswordResetController@find');
    //reset password
    Route::post('reset', 'PasswordResetController@reset');
});

//Cerevid's Routes --begin
Route::group([
    'prefix' => 'courses',
    'middleware' => 'auth:api'
], function(){
    Route::get('/favorites', 'Cerevids\FavoriteController@index')->name('favorites');
    Route::get('/lesson/{lesson_id}', 'Cerevids\CourseController@indexByLesson')->name('coursesByLesson');
    Route::post('/create', 'Cerevids\CourseController@create')->name('course/create');
    Route::get('/{id}', 'Cerevids\CourseController@find')->name('course/detail');
    Route::get('/teacher/{id}', 'Cerevids\CourseController@indexByTeacher');
    Route::put('/{id}', 'Cerevids\CourseController@update')->name('course/update');
    Route::delete('/{id}', 'Cerevids\CourseController@delete')->name('course/delete');
    Route::get('/{id}/learned', 'Cerevids\LearnedController@index');
    Route::post('/{id}/learned', 'Cerevids\LearnedController@store');

    Route::group([
        'prefix' => '/{course_id}/sections'
    ], function(){
        Route::get('/', 'Cerevids\SectionController@index')->name('sections');
        Route::post('/create', 'Cerevids\SectionController@create')->name('section/create');
        Route::get('/{section_id}', 'Cerevids\SectionController@find')->name('section/detail');
        Route::put('/{section_id}', 'Cerevids\SectionController@update')->name('section/update');
        Route::delete('/{section_id}', 'Cerevids\SectionController@delete')->name('section/delete');
    });

    Route::group([
        'prefix' => '/{course_id}/reviews'
    ], function(){
        Route::get('/', 'Cerevids\ReviewController@index')->name('reviews');
        Route::post('/create', 'Cerevids\ReviewController@create')->name('review/create');
        Route::get('/{review_id}', 'Cerevids\ReviewController@find')->name('review/detail');
        Route::put('/{review_id}', 'Cerevids\ReviewController@update')->name('review/update');
        Route::delete('/{review_id}', 'Cerevids\ReviewController@delete')->name('review/delete');
    });

    Route::group(['prefix' => '/{course_id}/forums'], function(){
        Route::get('/', 'Cerevids\ForumController@index')->name('forums');
        Route::post('/create', 'Cerevids\ForumController@create')->name('forum/create');
        Route::get('/{forum_id}', 'Cerevids\ForumController@find')->name('forum/detail');
        Route::put('/{forum_id}', 'Cerevids\ForumController@update')->name('forum/update');
        Route::delete('/{forum_id}', 'Cerevids\ForumController@delete')->name('forum/delete');
    });

    Route::group(['prefix' => '/{course_id}/favorites'], function(){
        Route::post('/create', 'Cerevids\FavoriteController@create')->name('favorite/create');
        Route::get('/{favorite_id}', 'Cerevids\FavoriteController@find')->name('favorite/detail');
        Route::delete('/{favorite_id}', 'Cerevids\FavoriteController@delete')->name('favorite/delete');
    });
});

Route::group([
    'prefix' => 'materi/{id}',
    'middleware' => 'auth:api'
], function() {
    Route::post('/seen', 'Cerevids\SectionController@lastSeen')->name('sections/seen');
});

Route::group([
    'prefix' => 'sections/{section_id}',
    'middleware' => 'auth:api'
], function(){
    Route::get('/videos', 'Cerevids\VideoController@index')->name('videos');
    Route::post('/videos/create', 'Cerevids\VideoController@create')->name('video/create');
    Route::get('/videos/{video_id}', 'Cerevids\VideoController@find')->name('video/detail');
    Route::put('/videos/{video_id}', 'Cerevids\VideoController@update')->name('video/update');
    Route::delete('/videos/{video_id}', 'Cerevids\VideoController@delete')->name('video/delete');

    Route::get('/texts', 'Cerevids\TextController@index')->name('texts');
    Route::post('/texts/create', 'Cerevids\TextController@create')->name('text/create');
    Route::get('/texts/{text_id}', 'Cerevids\TextController@find')->name('text/detail');
    Route::put('/texts/{text_id}', 'Cerevids\TextController@update')->name('text/update');
    Route::delete('/texts/{text_id}', 'Cerevids\TextController@delete')->name('text/delete');

    Route::get('/quiz', 'Cerevids\QuizController@index')->name('quiz');
    Route::post('/quiz/create', 'Cerevids\QuizController@create')->name('quiz/create');
    Route::get('/quiz/{quiz_id}', 'Cerevids\QuizController@find')->name('quiz/detail');
    Route::put('/quiz/{quiz_id}', 'Cerevids\QuizController@update')->name('quiz/update');
    Route::delete('/quiz/{quiz_id}', 'Cerevids\QuizController@delete')->name('quiz/delete');
});

Route::group([
    'prefix' => 'quiz/{quiz_id}',
    'middleware' => 'auth:api'
], function(){
    Route::post('/create_question', 'Cerevids\QuizController@createQuestion')->name('quiz/createquest');
    Route::get('/show_question/{question_id}', 'Cerevids\QuizController@showQuestion')->name('quiz/showquest');
    Route::put('/update_question/{question_id}', 'Cerevids\QuizController@updateQuestion')->name('quiz/updatequest');
    Route::delete('/delete_question/{question_id}', 'Cerevids\QuizController@deleteQuestion')->name('quiz/deletequest');
});
//Cerevid's Routes --end

//Cereout's Routes --begin
Route::group(['prefix' => 'cereouts', 'middleware' => 'auth:api'], function(){
    Route::get('/running', 'Cereouts\CereoutController@getRunningTryout');
    Route::get('/question/{id}', 'Cereouts\QuestionController@index')->name('questions');
    Route::get('/', 'Cereouts\TryoutController@index')->name('tryouts');
    Route::get('/class/{id}', 'Cereouts\TryoutController@indexByClass');
    Route::post('/create', 'Cereouts\TryoutController@create')->name('tryout/create');
    Route::get('/{id}', 'Cereouts\TryoutController@find')->name('tryout/detail');
    Route::put('/{id}', 'Cereouts\TryoutController@update')->name('tryout/update');
    Route::delete('/{id}', 'Cereouts\TryoutController@delete')->name('tryout/delete');
    Route::get('/attempttryout/{id}', 'Cereouts\AttemptTryoutController@getTryoutUser');
    Route::get('/attempttryout/class/{id}', 'Cereouts\AttemptTryoutController@getTryoutUserClass');
    Route::get('/attempttryout/{id}/expire', 'Cereouts\AttemptTryoutController@getExpireTryoutUser');
    Route::get('/attempttryout/class/{id}/expire', 'Cereouts\AttemptTryoutController@getExpireTryoutUserClass');
    Route::get('/result/{id}', 'Cereouts\CereoutController@getCereoutByUser');
    Route::get('/tryout/{id}/result', 'Cereouts\CereoutController@getCereoutByTryout');
    Route::get('/result/{id}/tryout/{tryout_id}/summary/{user_id}', 'Cereouts\CereoutController@getSummaryTryout');
    Route::get('/result/detail/{id}', 'Cereouts\CereoutController@getDetailCereoutByUser');

    Route::group(['prefix' => '/{tryout_id}/attempts'], function(){
        Route::get('/', 'Cereouts\CereoutController@index')->name('cereouts');
        Route::get('/mine', 'Cereouts\CereoutController@indexByUser')->name('cereoutsByUser');
        Route::get('/rankings', 'Cereouts\CereoutController@ranking')->name('cereout/ranking');
        Route::post('/', 'Cereouts\CereoutController@attempt')->name('cereout/attempt');
        Route::get('/{id}', 'Cereouts\CereoutController@find')->name('cereout/detail');
        Route::post('/{id}/valuation', 'Cereouts\CereoutController@valuation')->name('cereout/valuation');
        Route::delete('/{id}', 'Cereouts\CereoutController@delete')->name('cereout/delete');
    });

    Route::group(['prefix' => '/leaderboard'], function(){
        Route::get('/{id}', 'Cereouts\LeaderboardController@getLeaderboardByClass');
        Route::get('/lesson/{id}', 'Cereouts\LeaderboardController@getLeaderboardByLesson');
        Route::get('/ranking/{id}', 'Cereouts\LeaderboardController@getRanking');
    });

    Route::group(['prefix' => '/chart'], function(){
        Route::get('/lesson/{id}', 'Cereouts\LeaderboardController@getChartByLesson');
        Route::get('/class/{id}', 'Cereouts\LeaderboardController@getChartByClass');
    });
});
//Cereout's Routes --end

Route::group([
    'prefix' => 'payment',
    'middleware' => 'auth:api'
], function(){
    Route::post('/create', 'Payment\PaymentController@submitPayment');
    Route::put('/update/{id}', 'Payment\PaymentController@UpdatePayment');
    Route::get('/{id}', 'Payment\PaymentController@getTransactionByUser');
});

//cerecall routes
Route::group([
    'prefix' => 'cerecall',
    'middleware' => 'auth:api'
], function(){
    Route::post('/history', 'Cerecalls\CerecallController@postHistoryCall');
    Route::put('/history/{id}', 'Cerecalls\CerecallController@updateHistoryCall');
    Route::put('/history/status/{id}', 'Cerecalls\CerecallController@updateStatusKonsultasi');
    Route::get('/teacher/history', 'Cerecalls\CerecallController@getHistoryTeacher');
    Route::get('/teacher/history/running', 'Cerecalls\CerecallController@getRunningKonsultasiTeacher');
    Route::get('/student/history', 'Cerecalls\CerecallController@getHistoryStudent');
    Route::get('/student/history/running', 'Cerecalls\CerecallController@getRunningKonsultasiStudent');
    Route::get('/available/teacher/{id}', 'Cerecalls\CerecallController@getAvailableTeacher');
    Route::post('/report/{id}', 'Cerecalls\CerecallController@postReportTeacher');
    Route::put('/status', 'Cerecalls\CerecallController@changeStatus');
    Route::post('/chat/{id}', 'Cerecalls\CerecallController@postChatByKonsultasi');
    Route::get('/chat/{id}', 'Cerecalls\CerecallController@getChatByKonsultasi');
    Route::get('/teacher/performance', 'Cerecalls\CerecallController@getPerformanceTeacher');
    Route::get('/teacher/confirm', 'Cerecalls\CerecallController@getConfirmConsultationTeacher');
});

//master data Routes
Route::group(['prefix' => 'master'], function(){
    Route::get('/class', 'Master\ClassController@index');
    Route::get('/membership', 'Payment\PaymentController@getMembership');
    Route::get('/lesson', 'Cerevids\EnvironmentController@lessons');
    Route::get('/university', 'Master\UniversityController@index');
    Route::get('/department', 'Master\DepartmentController@index');
    Route::get('/faculty', 'Master\FacultyController@index');
    Route::get('/information', 'Master\InformationController@index');
    Route::get('/generalInformation', 'Master\GeneralInformationController@index');
    Route::get('/getAllDataUniversity', 'Master\UniversityController@getAlldata');
    Route::get('/nominal', 'Master\GeneralInformationController@getNominalTopUp');
});

//Cerelisasi routes --begin
Route::group([
    'prefix' => 'cerelisasi',
    'middleware' => 'auth:api'
], function() {
    Route::post('/analysis', 'Cerelisasi\CerelisasiController@analysis')->name('analysis');
    Route::get('/analysis', 'Cerelisasi\CerelisasiController@analyticsData')->name('analysisResult');
    Route::post('/reset_analysis', 'Cerelisasi\CerelisasiController@resetAnalytics')->name('resetAnalytics');
});

Route::group([
    'prefix' => 'cerepost',
    'middleware' => 'auth:api'
], function() {
    Route::get('/', 'Cerepost\CerepostController@index');
    Route::get('/post', 'Cerepost\PostController@index');
    Route::get('/post/{id}', 'Cerepost\PostController@indexByCerepost');
    Route::get('/post/show/{id}', 'Cerepost\PostController@show');
    Route::post('/post/{id}', 'Cerepost\PostController@store');
    Route::delete('/post/{id}', 'Cerepost\PostController@destroy');
    Route::get('/post/like/{id}', 'Cerepost\LikeController@index');
    Route::post('/post/like/{id}', 'Cerepost\LikeController@store');
    Route::delete('/post/unlike/{id}', 'Cerepost\LikeController@destroy');
    Route::get('/post/comment/{id}', 'Cerepost\CommentController@index');
    Route::post('/post/comment/{id}', 'Cerepost\CommentController@store');
    Route::delete('/post/comment/{id}', 'Cerepost\CommentController@destroy');
});


