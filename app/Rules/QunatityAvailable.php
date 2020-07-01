<?php

namespace App\Rules;

use App\Models\Item;
use Illuminate\Contracts\Validation\Rule;

class QunatityAvailable implements Rule
{
    private $quantity;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($item_id)
    {
        $this->quantity = Item::findOrFail($item_id)->quantity;
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
        return 1;
        // return ($value <= $this->quantity);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The required quantity isn\'t available at this moment.';
    }
}
