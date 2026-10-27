<div class="input-field">
    <input id="entry_date_time" name="entry_date_time" type="datetime-local" value="{{ $post['entry_date_time'] ?? (isset($entry) ? $entry->getDateTime() : '') }}" />
    <label class="active" for="entry_date_time">Entry date and time</label>
    <span class="helper-text">{{ $session->getTimezone() ?? 'UTC' }}. Leave blank to use the current time for a new entry, or keep an existing entry's date.</span>
</div>
