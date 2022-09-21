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
        relative_urls: false,
        images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '{{ $media_upload_post_url }}');

            xhr.upload.onprogress = (e) => {
                progress(e.loaded / e.total * 100);
            };

            xhr.onload = () => {
                if (xhr.status === 403) {
                    reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                    return;
                }

                if (xhr.status < 200 || xhr.status >= 300) {
                    reject('HTTP Error: ' + xhr.status + ' ' + xhr.responseText);
                    return;
                }

                const json = JSON.parse(xhr.responseText);

                if (!json || typeof json.location != 'string') {
                    reject('Invalid JSON: ' + xhr.responseText);
                    return;
                }

                resolve(json.location);
            };

            xhr.onerror = () => {
                reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
            };

            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            xhr.send(formData);
        })
    });
</script>