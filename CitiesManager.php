<?php

namespace App\Managers;

use App\City;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

class CitiesManager
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    public function findAllWithCinemas()
    {
        return City::query()
            ->with([
                "cinemas" => function (HasMany $builder) {
                    return $builder
                        ->where("cinemas.id", "=", "39835")
                        ->orWhere("cinemas.id", "=", "39038")
                        ->orWhere("cinemas.id", "=", "17028")
                        ->orWhere("cinemas.id", "=", "17984")
                        ->orWhere("cinemas.id", "=", "17344")
                        ->orWhere("cinemas.id", "=", "13811")
                        ->orderBy("title");
                },
            ])
            ->where("cities.id", "=", "2")
            ->orWhere("cities.id", "=", "3")
            ->orWhere("cities.id", "=", "2563")
            ->orWhere("cities.id", "=", "2540")
            ->orderBy("order")
            ->get();
    }

    public function findAllWithCinemasToFile()
    {
        $grouped = $this->findAllWithCinemas()
            ->makeHidden(['id', 'latitude', 'longitude', 'created_at', 'updated_at'])
            ->toJson();

//        File::put('cinemas.json', $grouped);
    }
}