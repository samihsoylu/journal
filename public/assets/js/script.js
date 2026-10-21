document.addEventListener('DOMContentLoaded', () => {
    M.Sidenav.init(document.querySelectorAll('.sidenav'));
    M.Dropdown.init(document.querySelectorAll('.dropdown-trigger'), { constrainWidth: false });
    M.FormSelect.init(document.querySelectorAll('form select'));
    M.Datepicker.init(document.querySelectorAll('.datepicker'), { format: 'mmm dd, yyyy' });
    M.Modal.init(document.querySelectorAll('.modal'));
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));

    document.querySelectorAll('.corner-alert').forEach(alert => {
        alert.addEventListener('click', () => { alert.hidden = true; });
    });
    document.querySelectorAll('#desktopFilter, #filters').forEach(form => {
        form.addEventListener('submit', () => {
            form.querySelectorAll('input, select, textarea').forEach(input => {
                if (!input.value) input.removeAttribute('name');
            });
        });
    });
});
