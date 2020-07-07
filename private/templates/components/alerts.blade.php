    @isset($error)
        <div class="card-panel red white-text valign-wrapper"><i class="material-icons left">error</i> {{ $error }}</div>
    @endisset

    @isset($success)
        <div class="card-panel green white-text valign-wrapper"><i class="material-icons left">check_circle</i> {{ $success }}</div>
    @endisset

    @isset($warning)
        <div class="card-panel orange black-text valign-wrapper"><i class="material-icons left">warning</i> {{ $warning }}</div>
    @endisset

    @isset($info)
        <div class="card-panel blue white-text valign-wrapper"><i class="material-icons left">info</i> {{ $info }}</div>
    @endisset