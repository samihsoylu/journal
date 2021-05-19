@extends('base')

@section('pageTitle', 'Account')

@section('content')
    @include('components/header')

    <div class="container">
        <div class="row row-align">
            <div class="col s12 m6">

                <!-- Change Password -->
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Change your password</span>
                        <p class="small-text"><b>Remember:</b> Your password cannot be reset if you forget it</p>
                        <form method="post" action="{{ $change_password_post_url }}/">
                            <div class="input-field">
                                <input id="currentPassword" name="currentPassword" type="password" required />
                                <label for="currentPassword">Current password</label>
                            </div>
                            <div class="input-field">
                                <input id="newPassword" name="newPassword" type="password" required />
                                <label for="newPassword">New password</label>
                            </div>
                            <div class="input-field">
                                <input id="confirmPassword" name="confirmPassword" type="password" required />
                                <label for="confirmPassword">Confirm password</label>
                            </div>

                            <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />

                            <button class="btn btn-default" type="submit"><i class="material-icons">lock</i> Change password</button>
                        </form>
                    </div>
                </div>

                <!-- Enable / Disable widgets -->
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Widgets</span>
                        <form method="post" action="{{ $update_widgets_post_url }}">
                            <p class="checkbox">
                                <label>
                                    <input type="checkbox" name="quickAddEntriesBoxEntriesOverview" class="filled-in" @isset($enabledWidgets['quickAddEntriesBoxEntriesOverview']) checked="checked" @endisset />
                                    <span>Enable quick add box in entries overview page (not available on mobile view)</span>
                                </label>
                            </p>
                            <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
                            <button class="btn btn-default" type="submit"><i class="material-icons">lock</i> Save widgets</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col s12 m6">

                <!-- Change email address -->
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Change your email address</span>
                        <form method="post" action="{{ $update_email_post_url }}">
                            <div class="input-field">
                                <input id="email" type="email" name="email" placeholder="{{ $user->getEmailAddress() }}" required />
                                <label for="email">Email address</label>
                            </div>

                            <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />

                            <button class="btn btn-default" type="submit"><i class="material-icons">mail_outline</i> Change email</button>
                        </form>
                    </div>
                </div>

                <!-- Delete account -->
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Delete your account</span>
                        <p class="small-text"><b>Warning:</b> This action cannot be reversed</p>
                        <form method="post" action="{{ $delete_account_post_url }}">
                            <div class="input-field">
                                <input id="password" type="password" name="password" required />
                                <label for="password">Current password</label>
                            </div>

                            <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />

                            <button class="btn btn-danger" id="delete" type="submit"><i class="material-icons">delete_forever</i> Delete account</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection