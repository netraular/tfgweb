<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    //
    public function languageSwitch($locale){
    // Validate that the locale exists in your application's supported locales
    if (in_array($locale, ['en', 'es'])) {
        // Set the application's locale to the selected locale
        session(['language'=>$locale]);
    }
    // Redirect back to the previous page or any desired page
    return back()->with(['language_switched'=>$locale]);
    }
}
