<form id="delete-record" method="post" action="{{ $deleteAction }}" hidden>
    <input type="hidden" name="form_key" value="{{ $session->getAntiCSRFToken() }}" />
</form>
