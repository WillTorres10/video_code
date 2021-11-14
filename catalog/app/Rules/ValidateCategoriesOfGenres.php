<?php

namespace App\Rules;

use App\Models\{Category, Genre};
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class ValidateCategoriesOfGenres implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $categories = new Collection(request()->get('categories_id'));
        $idCategoriesGenres = $this->getIdCategoriesGenres();
        $notIn = $categories->filter(fn($category) => !$idCategoriesGenres->contains($category));
        return $notIn->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('validation.categories.no_related');
    }

    protected function getIdCategoriesGenres()
    {
        $idCategoriesGenres = [];
        $genres = Genre::whereIn('id', request()->get('genres_id'))
            ->with(['categories' => fn($query) => $query->select('id')])
            ->get();
        foreach ($genres as $genre) {
            foreach ($genre->categories as $category) {
                if(!in_array($category->id, $idCategoriesGenres)) {
                    $idCategoriesGenres[] = $category->id;
                }
            }
        }
        return new Collection($idCategoriesGenres);
    }
}
