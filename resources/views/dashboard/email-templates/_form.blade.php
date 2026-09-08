@php
    $template = $template ?? null;
@endphp

@csrf

<input type="hidden" name="content" id="email-template-content" value="{{ old('content', $template?->content) }}" />

<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="mb-3 col-md-6 mb-md-0">
                <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                    name="name" value="{{ old('name', $template?->name) }}" placeholder="e.g. Welcome Email"
                    autofocus />
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 col-md-6 mb-md-0">
                <label for="subject" class="form-label">Email Subject</label>
                <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject"
                    name="subject" value="{{ old('subject', $template?->subject) }}"
                    placeholder="e.g. Welcome to our newsletter!" />
                @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0">Drag-and-Drop Builder</h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-template">
                <i class="bx bx-trash me-1"></i> Clear
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="load-example">
                <i class="bx bx-magic-wand me-1"></i> Load Example
            </button>
        </div>
    </div>
    <div class="card-body py-2">
        @error('content')
            <div class="alert alert-danger py-2">
                {{ $message }}
            </div>
        @enderror
        <div id="gjs" class="w-100"></div>
    </div>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary me-2">
            <i class="bx bx-save me-1"></i> {{ $template ? 'Update Template' : 'Save Template' }}
        </button>
        <a href="{{ route('email-templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/grapes/grapes.min.css') }}" />
    <style>
        #gjs {
            height: 65vh;
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .gjs-one-bg {
            background-color: #f8f9fa;
        }

        .gjs-two-color {
            color: #495057;
        }

        .gjs-three-bg {
            background-color: #eceef1;
        }

        .gjs-four-color,
        .gjs-four-color-h:hover {
            color: #344767;
        }

        .gjs-pn-btn,
        .gjs-block {
            color: #495057;
        }

        .gjs-block {
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            transition: box-shadow 0.15s ease;
        }

        .gjs-block:hover {
            box-shadow: 0 0 0 2px rgba(105, 108, 255, 0.4);
        }

        .gjs-cv-canvas {
            background-color: #e8eaed;
        }

        .gjs-block svg {
            fill: #696cff;
        }

        .gjs-block-media {
            font-size: 26px;
            color: #696cff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/grapes/grapes.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            if (typeof grapesjs === 'undefined') {
                console.error('GrapesJS is not loaded');
                return;
            }

            const gjsElement = document.getElementById('gjs');

            if (!gjsElement) {
                console.error('GrapesJS container #gjs not found');
                return;
            }

            const editor = grapesjs.init({
                container: '#gjs',
                height: '65vh',
                width: 'auto',
                fromElement: false,
                storageManager: false,

                canvas: {
                    styles: [
                        'body { margin: 0; padding: 0; }',
                        'table { border-collapse: collapse; }',
                        'img { border: 0; display: block; max-width: 100%; }'
                    ]
                }
            });


            /*
            |--------------------------------------------------------------------------
            | Blocks
            |--------------------------------------------------------------------------
            */

            editor.BlockManager.add('email-section', {
                label: 'Section',
                category: 'Layout',
                media: '<i class="bx bx-columns"></i>',
                content: '<div style="padding:20px;"></div>'
            });


            editor.BlockManager.add('email-section-2col', {
                label: 'Two Columns',
                category: 'Layout',
                media: '<i class="bx bx-table"></i>',
                content: '<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"><tr><td width="50%" style="padding:15px;">Column 1</td><td width="50%" style="padding:15px;">Column 2</td></tr></table>'
            });


            editor.BlockManager.add('email-heading', {
                label: 'Heading',
                category: 'Text',
                media: '<i class="bx bx-heading"></i>',
                content: '<h1 style="font-family:Arial; font-size:26px; color:#384151;">' +
                    'Your Heading Here' +
                    '</h1>'
            });


            editor.BlockManager.add('email-text', {
                label: 'Text',
                category: 'Text',
                media: '<i class="bx bx-text"></i>',
                content: '<p style="font-family:Arial; font-size:15px; line-height:1.6;">' +
                    'Write your content here. Double-click to edit.' +
                    '</p>'
            });


            editor.BlockManager.add('email-divider', {
                label: 'Divider',
                category: 'Text',
                media: '<i class="bx bx-minus"></i>',
                content: '<hr style="border:none; border-top:1px solid #eceef1; margin:24px 0;" />'
            });


            editor.BlockManager.add('email-image', {
                label: 'Image',
                category: 'Basic',
                media: '<i class="bx bx-image"></i>',
                content: '<img src="https://via.placeholder.com/600x300?text=Your+Image" alt="Your Image" style="width:100%; max-width:600px; display:block;" />'
            });


            editor.BlockManager.add('email-button', {
                label: 'Button',
                category: 'Basic',
                media: '<i class="bx bx-pointer"></i>',
                content: '<a href="#" style="' +
                    'background:#696cff;' +
                    'color:#ffffff;' +
                    'padding:12px 24px;' +
                    'display:inline-block;' +
                    'text-decoration:none;' +
                    'border-radius:6px;' +
                    '">' +
                    'Call To Action' +
                    '</a>'
            });


            editor.BlockManager.add('email-spacer', {
                label: 'Spacer',
                category: 'Basic',
                media: '<i class="bx bx-vertical-center"></i>',
                content: '<div style="height:40px;"></div>'
            });


            editor.BlockManager.add('email-social', {
                label: 'Social Links',
                category: 'Basic',
                media: '<i class="bx bx-share-alt"></i>',
                content: '<div style="padding:10px 0;">' +
                    '<a href="#" style="margin-right:10px; color:#696cff; text-decoration:none;">Twitter</a>' +
                    '<a href="#" style="margin-right:10px; color:#696cff; text-decoration:none;">Facebook</a>' +
                    '<a href="#" style="color:#696cff; text-decoration:none;">LinkedIn</a>' +
                    '</div>'
            });


            const blocksPanelBtn = editor.Panels.getButton('views', 'open-blocks');

            if (blocksPanelBtn) {
                blocksPanelBtn.set('active', true);
            }


            /*
            |--------------------------------------------------------------------------
            | Load Existing Content
            |--------------------------------------------------------------------------
            */

            const contentInput =
                document.getElementById('email-template-content');


            function loadIntoEditor(html) {

                if (!html) {
                    return;
                }

                try {

                    const parser = new DOMParser();

                    const documentHtml =
                        parser.parseFromString(
                            html,
                            'text/html'
                        );


                    let bodyContent = html;

                    if (documentHtml.body) {
                        bodyContent =
                            documentHtml.body.innerHTML;
                    }


                    let css = '';

                    const styles =
                        documentHtml.querySelectorAll('style');


                    styles.forEach(function(style) {

                        css += style.innerHTML + '\n';

                    });


                    editor.setComponents(bodyContent);

                    if (css) {
                        editor.setStyle(css);
                    }

                } catch (error) {

                    console.error(
                        'Error loading template',
                        error
                    );

                }

            }


            if (
                contentInput &&
                contentInput.value
            ) {

                loadIntoEditor(
                    contentInput.value
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Save Template
            |--------------------------------------------------------------------------
            */

            const form =
                document.getElementById(
                    'email-template-form'
                );


            if (form) {

                form.addEventListener(
                    'submit',
                    function() {

                        if (!contentInput) {
                            return;
                        }


                        const html =
                            editor.getHtml();


                        const css =
                            editor.getCss();


                        const emailContent =
                            '<!DOCTYPE html>' +
                            '<html>' +
                            '<head>' +
                            '<meta charset="UTF-8">' +
                            '<meta name="viewport" content="width=device-width, initial-scale=1.0">' +
                            '<style>' +
                            css +
                            '</style>' +
                            '</' + 'head>' +
                            '<body>' +
                            html +
                            '</' + 'body>' +
                            '</' + 'html>';


                        contentInput.value =
                            emailContent;

                    }
                );

            } else {

                console.error(
                    '#email-template-form not found'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Clear Template
            |--------------------------------------------------------------------------
            */

            const clearButton =
                document.getElementById(
                    'clear-template'
                );


            if (clearButton) {

                clearButton.addEventListener(
                    'click',
                    function() {

                        const confirmClear =
                            confirm(
                                'Clear the canvas? The current design will be removed.'
                            );


                        if (confirmClear) {

                            editor.setComponents('');

                            editor.setStyle('');

                        }

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Load Example
            |--------------------------------------------------------------------------
            */

            const exampleButton =
                document.getElementById(
                    'load-example'
                );


            if (exampleButton) {

                exampleButton.addEventListener(
                    'click',
                    function() {

                        const currentHtml =
                            editor.getHtml().trim();


                        if (currentHtml) {

                            const replace =
                                confirm(
                                    'Replace the current design with the example?'
                                );


                            if (!replace) {
                                return;
                            }

                        }


                        const exampleHtml =
                            '<div style="' +
                            'background:#ffffff;' +
                            'padding:40px 30px;' +
                            'text-align:center;' +
                            '">' +

                            '<h1 style="' +
                            'font-family:Arial;' +
                            'font-size:28px;' +
                            'font-weight:bold;' +
                            'color:#384151;' +
                            '">' +

                            'Welcome to our newsletter' +

                            '</h1>' +

                            '<p style="' +
                            'font-family:Arial;' +
                            'font-size:15px;' +
                            'line-height:1.6;' +
                            'color:#677788;' +
                            '">' +

                            'Thanks for signing up! Expect great content, ' +
                            'product updates and exclusive offers in your inbox.' +

                            '</p>' +

                            '<a href="#" style="' +
                            'background:#696cff;' +
                            'color:#ffffff;' +
                            'padding:14px 28px;' +
                            'text-decoration:none;' +
                            'display:inline-block;' +
                            'border-radius:6px;' +
                            'font-family:Arial;' +
                            '">' +

                            'Get Started' +

                            '</a>' +

                            '</div>';


                        editor.setComponents(
                            exampleHtml
                        );


                        editor.setStyle('');

                    }
                );

            }


            console.log(
                'GrapesJS initialized successfully'
            );

        });
    </script>
@endpush
