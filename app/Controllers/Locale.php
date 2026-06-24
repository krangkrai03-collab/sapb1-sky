<?php

namespace App\Controllers;

class Locale extends BaseController
{
    /**
     * Quick per-session language switch (navbar). Falls back safely.
     */
    public function set(string $locale)
    {
        if (in_array($locale, config('App')->supportedLocales, true)) {
            if (auth()->loggedIn()) {
                // Remember on the user account.
                $users = new \App\Models\UserModel();
                $user  = $users->findById(auth()->id());
                $user->locale = $locale;
                $users->save($user);
            } else {
                session()->set('locale', $locale);
            }
        }

        return redirect()->back();
    }
}
