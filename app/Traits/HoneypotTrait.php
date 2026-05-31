<?php

namespace App\Traits;

use Illuminate\Http\Request;

/**
 * HoneypotTrait — reusable honeypot validation for controllers.
 *
 * Add a hidden input field named `_hp_website` to your forms.
 * Bots will fill it; real users won't see it.
 * If the field is filled, reject silently (return fake success).
 */
trait HoneypotTrait
{
    /**
     * Check if the honeypot field has been filled.
     *
     * @param Request $request
     * @return bool  true if bot detected (field is filled)
     */
    protected function isHoneypotFilled(Request $request): bool
    {
        $value = $request->input('_hp_website', '');

        return ! empty(trim((string) $value));
    }
}
