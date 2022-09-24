@isset($error)
        <div class="corner-alert z-depth-4 card-panel red white-text valign-wrapper"><i class="material-icons left">error</i> {{ $error }}</div>
    @endisset

    @isset($success)
        <div class="corner-alert z-depth-4 card-panel green white-text valign-wrapper"><i class="material-icons left">check_circle</i> {{ $success }}</div>
    @endisset

    @isset($warning)
        <div class="corner-alert z-depth-4 card-panel orange black-text valign-wrapper"><i class="material-icons left">warning</i> {{ $warning }}</div>
    @endisset

    @isset($info)
        <div class="corner-alert z-depth-4 card-panel blue white-text valign-wrapper"><i class="material-icons left">info</i> {{ $info }}</div>
    @endisset