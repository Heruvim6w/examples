<?php

namespace App\Http\Controllers;

use App\Cinema;
use App\City;
use App\Film;
use App\Footer;
use App\Format;
use App\Managers\CinemasManager;
use App\Managers\FilmsManager;
use App\Managers\UserCinemaInterface;
use App\Resume;
use App\Seance;
use App\Vacancy;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CinemasController extends Controller
{
    /**
     * @var UserCinemaInterface
     */
    private $userCinema;

    /**
     * @var FilmsManager
     */
    private $filmsManager;

    /**
     * @var CinemasManager
     */
    private $cinemasManager;

    public function __construct(UserCinemaInterface $userCinema, CinemasManager $cinemasManager, FilmsManager $filmsManager)
    {
        $this->userCinema = $userCinema;
        $this->filmsManager = $filmsManager;
        $this->cinemasManager = $cinemasManager;
    }

    public function index(Request $request): View
    {
        $myCinema = $this->cinemasManager->findMyCinema();
        $city = $myCinema->city;
        $this->cinemasManager->findByCity($city);
        $cinemas = $this->cinemasManager->getLuxorCinemas();
        $cinemas->map(function ($cinema) {
            if (substr($cinema->main_image, 0, 4) != 'http') {
                $cinema->main_image = '/storage/' . $cinema->main_image;
            }
            return $cinema;
        });
        return view("cinemas.index", compact("city", "cinemas", 'myCinema'));
    }

    public function show(Request $request, Cinema $cinema): View
    {
        $myCinema = $this->cinemasManager->findMyCinema();

        $city = $myCinema->city;

        $films = $this->filmsManager->findAllWithSeances(
            $cinema,
            $request->get("day", Carbon::today()->format("Y-m-d")),
            $request->get("technology", null),
            $request->get("hall", null)
        );
        foreach ($films as &$film) {
            $technologyMass = [];
            foreach ($film->seances as $seances) {
                if (strstr($seances->technology,",")) {
                    $temp = preg_split("/\s*,\s*/",trim($seances->technology));
                    foreach ($temp as $oneitem) {
                        $technologyMass[] = $oneitem;
                    }
                } else {
                    $technologyMass[] = trim($seances->technology);
                }

            }
            $film->technologies = array_unique($technologyMass);
        }

        $format_2d = Format::where('title', '2D')->first('description');
        $format_3d = Format::where('title', '3D')->first('description');
        $format_atmos = Format::where('title', 'Dolby Atmos')->first('description');
        $format_dolby = Format::where('title', 'Dolby')->first('description');
        $format_d_box = Format::where('title', 'D-Box')->first('description');

        return view("cinemas.show", compact("cinema", "city", "films", "format_2d", "format_3d", "format_atmos", "format_d_box", "format_dolby"));
    }

    public function indexEvents(Request $request, Cinema $cinema): View
    {
        $events = $cinema->events;

        return view("events.index", compact("events"));
    }

    public function indexStocks(Request $request, Cinema $cinema): View
    {
        $stocks = $cinema->stocks;

        return view("stocks.index", compact("stocks"));
    }

    public function indexVacancies(Request $request, Cinema $cinema): View
    {
        $myCinema = $this->cinemasManager->findMyCinema();

        $city = $myCinema->city;

        $vacancies = $cinema->vacancies;

        return view("vacancies.index", compact("vacancies", "myCinema"));
    }

    public function indexSeancesForDate(Request $request)
    {
        $cinema = $this->userCinema->findMyCinema();
        $films = $this->filmsManager->findAllWithSeances(
            $cinema,
            $request->get("day")
        );
        $films = IndexController::convert_index_film($films);
        return $films->toJson();
    }
    
    public function indexSeances(Request $request, Cinema $cinema, CinemasManager $cinemasManager): View
    {
        if (!$this->userCinema->isMyCinema($cinema)) {
            $this->userCinema->setMyCinema($cinema);
        }

        $months = [
            "январе",
            "феврале",
            "марте",
            "апреле",
            "мае",
            "июне",
            "июле",
            "августе",
            "сентябре",
            "октябре",
            "ноябре",
            "декабре"
        ];

        // день за который показываем фильмы
        $filmDate = Carbon::today();
        $userDate = $request->get("day", false);
        if ($userDate) {
            $filmDate = @Carbon::createFromFormat("Y-m-d", $userDate);
        }

        $dayList = collect();
        $this->filmsManager->findNowAndFutureFilms($cinema)
            ->pluck("seances")
            ->flatMap(function ($seances) {
                $seances->map(function ($seance){
                    if($seance->time < '02:00'){

                        $seance->day = Carbon::parse($seance->day)->subday()->format("Y-m-d");
                    }
                    return $seance;
                });
                return $seances;
            })
            ->pluck("day")
            ->unique()
            ->map(function ($value) use($dayList){
                $dayList->push([
                    $value,
                    Carbon::parse($value)->day,
                    1,
                    'day'=>$value,
                    'title'=>Carbon::parse($value)->day]
                );
                return $value;
        });

        $dayList = $dayList->SortBy('day');

        $duplicate = $dayList->duplicates('title');
        $dayList = $dayList->toArray();
        if($duplicate){
            $duplicate->map(function ($value,$key) use(&$dayList){
                $day = $dayList[$key][1];
                $month = Carbon::parse($dayList[$key][0])->month;
                $dayList[$key][1] = $month.'.'.$day;
            });
        }

        // переменные для вывода на страницу
        $monthName = $months[$filmDate->month - 1];
        $dayNumber = $filmDate->day;

        $films = $this->filmsManager->findAllWithSeances(
            $cinema,
            $request->get("day", $filmDate->format("Y-m-d")),
            $request->get("technology", null),
            $request->get("hall", null)
        );

        $halls = $films
            ->pluck("seances")
            ->flatMap
            ->pluck("hall")
            ->unique();

        $soon_cinema = $this->filmsManager->findAllSoon($cinema);

        $filmsNew = [];
        $filmsNew = collect();
        foreach ($films as $film) {
            $poster = "";
            foreach ($film->images as $image) {
                $poster = \Illuminate\Support\Facades\Storage::url($image['image']);
                break;
            }

            $seances = [];
            foreach ($film->seances as $seance) {
                $seances[] = [
                    "id" => $seance->id,
                    "time" => $seance->time,
                    "tech" => $seance->technology,
                    "hall" => $seance->hall,
                    "minprice" => $seance->minprice,
                    "maxprice" => $seance->maxprice,
                    "session_id" => $seance->session_id,
                    "places" => json_decode($seance->places),
                ];
            }

            $filmsNew->push([
                "id" => $film->id,
                "title" => $film->title,
                "title_original" => $film->title_original,
                "slug" => $film->slug,
                "country" => $film->country,
                "year" => $film->year,
                "director" => $film->director,
                "scenario" => $film->scenario,
                "starring" => $film->starring,
                "duration" => $film->duration,
                "age_rating" => $film->age_rating,
                "rating" => $film->rating,
                "genres" => preg_split("/\s*,\s*/",$film->genres),
                "release_date" => $film->release_date,
                "description" => $film->getShortDescriptionAttribute(),
                "status" => $film->status,
                "order" => $film->order,
                "images" => $film->images,
                "poster" => $poster,
                "videos" => $film->videos,
                "seances" => $seances,
                "count_seances" => $film->seances->count(),
            ]);
        }

        $filmsJson = json_encode($filmsNew->sortByDesc('count_seances')->values());
        return view("seances.index", compact("films", "soon_cinema", "halls", "dayList", "monthName", "dayNumber", "filmsJson"));
    }

    public function convert_seances_carousel_day($days_to_carousel){
        $days = $films->pluck("seances")
            ->flatMap
            ->pluck("day")
            ->unique()
            ->sort()
            ->values();

        $films->map(function ($film) use($days){

            foreach ($days as $key=>$day){

                $next_day = []; // этой переменной мы добавляем 2 часа с следующего дня к предыдущему
                if(isset($days[$key+1])){
                    $next_day =
                        $film->seances->where('day',$days[$key+1])
                            ->where('time','<','02:00')
                            ->toarray();
                }
                $film->$day = array_merge(
                    $film->seances
                        ->where('day',$day)
                        ->where('time','>','02:00')
                        ->values()
                        ->toarray(),
                    $next_day
                );
            }
            return $film;
        });

        return  $films;
    }

    public function resumes(Request $request, Cinema $cinema, CinemasManager $cinemasManager)
    {
        if($request->isMethod('post')){

            if($request->hasFile('file')) {
                $file = $request->file('file');
                $file->move(public_path() . '/path', $file->getClientOriginalName());
                $file_name = $file->getClientOriginalName();
            }
            else{
                $file_name = 'нет файла';
            }
            $city = City::find($cinema->city_id)->first()->title;
            $vacancy_name = Vacancy::find($request->vacancy_id)->first()->title;
            $resume = Resume::create(array(
                    'resume' => $file_name,
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'cinema' => $cinema->title,
                    'city' => $city,
                    'vacancy_id' => $request->vacancy_id,
                ));
                $resume->save();
                $footer = Footer::first();
                SendMailController::sendMail('emails.resumes',$footer->mail_vac,'Новая вакансия',[
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                    'cinema' => $cinema->title,
                    'vacancy_name' => $vacancy_name,
                    'city' => $city
                ]);
        }
        return response('success');
    }
}
