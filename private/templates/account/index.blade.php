@extends('base')

@section('pageTitle', 'Account settings')

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
                                    <span>Enable quick add box in <a href="{{ $entries_url }}">entries overview page</a> (not available on mobile view)</span>
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

                <!-- Export data from account -->
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Export all your entries</span>
{{--                        <p class="small-text">Your entries will be exported into markdown(.md) files, and later zipped into an archive(.zip) file. You can open markdown files with any text editor, but for a nice editing experience, you can use <a href="https://typora.io/" target="_blank">Typora</a>.</p>--}}
                            @foreach($exportedFiles as $file)
                                <div class="account-downloads">
                                        <div class="collection-item">
                                            @php
                                            $deleteFormId = "delete-zip-" . str_replace('.','', $file);
                                            @endphp
                                            <form id="{{ $deleteFormId }}" action="{{ $export_delete_post_url }}" method="post">
                                                <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
                                                <input type="hidden" name="fileName" value="{{ $file }}" />
                                                <span title="Delete" class="new badge btn-danger cursor-pointer" data-badge-caption="" onclick="document.forms['{{ $deleteFormId }}'].submit()"><i class="material-icons">delete</i></span>
                                            </form>
                                            <a href="{{ $export_download_url }}/{{ $file }}"><span title="Download" class="new badge btn-primary" data-badge-caption=""><i class="material-icons">file_download</i></span></a>
                                            <p>{{ $file }}</p>
                                        </div>
                                </div>
                            @endforeach
                        <form method="post" action="{{ $export_entries_post_url }}">
                            <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />

                            <button class="btn btn-primary mt-10" id="export" type="submit" @if ($exportedFiles !== []) disabled @endif><i class="material-icons">file_download</i> Export</button>
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