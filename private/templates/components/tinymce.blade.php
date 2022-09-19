<script type="text/javascript" src="{{ $assets_url }}/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script type="text/javascript">
    tinymce.init({
        selector: 'textarea#entry_content',
        plugins: 'importcss autolink code fullscreen image link media nonbreaking anchor lists wordcount emoticons quickbars table',
        menubar: false,
        toolbar: 'undo redo | bold italic underline strikethrough image link | blocks | align table numlist bullist emoticons fullscreen  | code outdent indent',
        quickbars_selection_toolbar: 'bold italic forecolor quicklink blockquote image',
        quickbars_insert_toolbar: false,
        toolbar_sticky: true,
        height: 400,
        image_caption: true,
        toolbar_mode: 'sliding',
        content_css: '{{ $assets_url }}/css/tinymce.css',
        block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3',
        images_upload_url: '{{ $media_upload_post_url }}',
        relative_urls: false,
    });
</script>