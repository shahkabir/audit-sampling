<?php
    namespace App\Models;

    class Listings{
        public static function all()
        {
            return[
                    [
                        'id' => 1,
                        'title' => 'Listing One',
                        'desc' => 'This is description'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Listing Two',
                        'desc' => 'This is description'
                    ]
                ];
        }

        public static function find($id)
        {
             //dd($id);
             $listings = self::all();

             foreach($listings as $listing)
             {
                if($listing['id'] == $id)
                return $listing;
             }
        }
    }