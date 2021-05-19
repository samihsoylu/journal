<div class="card hide-on-small-only">
    <div class="card-content row">
        <form method="post" action="{{ $create_entry_post_url }}">
            <div class="input-field col s3">
                <select id="category_id_d" name="category_id">
                    @foreach ($categories as $category)
                        <option value="{{ $category->getId() }}">{{ $category->getName() }}</option>
                    @endforeach
                </select>
                <label for="category_id">Category</label>
            </div>
            <div class="input-field col s3">
                <input id="entry_title" name="entry_title" type="text" class="validate"  required />
                <label for="entry_title">Entry title</label>
            </div>
            <div class="input-field col s4">
                <input id="entry_content" name="entry_content" type="text" class="validate"  required />
                <label for="entry_content">Entry content</label>
            </div>
            <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
            <input type="hidden" name="redirectToEntriesOverview" value="true" />
            <div class="col s2">
                <button class="btn btn-default mt-30 fullwidth" type="submit"><i class="material-icons">add</i> Add</button>
            </div>
        </form>
    </div>
</div>