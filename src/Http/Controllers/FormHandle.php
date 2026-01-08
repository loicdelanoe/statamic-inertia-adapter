<?php

namespace StatamicInertiaAdapter\StatamicInertiaAdapter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;

class FormHandle extends Controller
{
    public function handle(Request $request, string $handle)
    {
        $form = Form::find($handle);

        $honeypot = $form->honeypot();

        if ($request->filled($honeypot)) {
            return back();
        }

        $blueprint = $form->blueprint();

        $validated = $blueprint->fields()->addValues($request->all())->validate();

        FormSubmission::make()
            ->form($form)
            ->data($validated)
            ->save();

        return back();
    }
}
