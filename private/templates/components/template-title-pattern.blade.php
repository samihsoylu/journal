<div class="input-field">
    <input id="default_title_pattern" name="default_title_pattern" type="text" maxlength="128" value="{{ $post['default_title_pattern'] ?? (isset($template) ? $template->getDefaultEntryTitlePattern() : '') }}" />
    <label for="default_title_pattern">Default entry title (optional)</label>
    <span class="helper-text">For example, Sleep {date}. Use {date}, {time} or {template}. You can change the title before saving.</span>
</div>
