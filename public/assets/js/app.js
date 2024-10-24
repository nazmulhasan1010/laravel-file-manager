$(function () {
    const tooltipTriggerList = $('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    const wm = new bootstrap.Modal('#arrange-modal');
    let clipBoard = {};

    async function loadSettings() {
        let response = await fetch('assets/js/settings.json');
        return await response.json()
    }

    $('.search-icon').click(function () {
        $('.search').toggleClass('collapse');
    })

    foldersIcon();

    async function opener(e) {
        let folder = $(e).parent('.folder');
        if (!$(folder).data('load'))
            await placeItems(folder);
        $(e).parent('.folder').toggleClass('opened');
        $(e).siblings('.folder-items').toggleClass('closed');
        placeIcon($(e), true)
    }

    async function placeItems(folder, listView = false, dataPath = null) {
        return new Promise((resolve, reject) => {
            let path = dataPath ?? $(folder).data('path');
            loadSettings().then(data => {
                const theme = data.theme;
                const view = data.view;
                axios.post(`nf-file-manager/items`, {path: path}).then(function (response) {
                    let data = response.data,
                        fItems = $('<ul>', {class: 'folder-items'}), fItem, lItem, gItem;

                    if (listView) {
                        $('#list-view-table').empty();
                        $('#list-view-grid').empty();
                    } else {
                        $(folder).children('.folder-items').remove();
                        $(folder).attr('data-load', 'true')
                    }
                    $.each(data, (i, item) => {
                        if (listView) {
                            availableOption();
                            $('.list-view-opened-folder').text($(folder).children('span').text())
                            let info = getTypeIconByExt(item.ext);
                            let icon = item.type === 'folder' ? 'bx-folder' : info.icon;
                            let tClass = item.type === 'folder' ? 'folder' : 'file';
                            lItem = $('<tr>', {
                                'data-item': item.items,
                                'data-path': item.path,
                                class: tClass,
                                html: `<td><i class="bx ${icon}"></i>${item.name}</td>
                                <td>${item.type}</td>
                                <td>${item.size}</td>`,
                            })

                            gItem = $('<a>', {
                                'data-item': item.items,
                                'data-path': item.path,
                                class: `list-item ${tClass}`,
                                html: `<i class="bx ${icon}"></i>
                                <p>${item.name}</p>`
                            })

                            if (view === 'list') {
                                $('#list-view-table').append(lItem)
                            } else if (view === 'grid') {
                                $('#list-view-grid').append(gItem)
                            }

                        } else {
                            if (item.type === 'folder') {
                                fItem = $('<li>', {
                                    class: 'folder opened',
                                    html: `<i class='opener bx bx-chevron-right'></i><span><i class='bx bx-folder'></i>${item.name}</span>`,
                                    'data-item': item.items,
                                    'data-path': item.path,
                                })
                            } else if (item.type === 'file') {
                                let info = getTypeIconByExt(item.ext);
                                fItem = $('<li>', {
                                    class: 'file',
                                    html: `<span><i class='bx ${info.icon}'></i>${item.name}</span>`
                                })
                            }
                            $(folder).append($(fItems).append(fItem))
                        }
                    })
                })
            })
            resolve()
        })
    }

    function foldersIcon() {
        let $folders = $('.files .folder span:not(.file span)');
        let $files = $('.files .file');

        $folders.each(function (i, item) {
            placeIcon(item)
        })

        $files.each(function (i, item) {
            const ext = $(this).data('ext');
            let info = getTypeIconByExt(ext),
                icon = $('<i>', {
                    class: `bx ${info.icon}`
                })
            $(this).children('span').prepend(icon)
        })
    }

    function placeIcon(item, ch = false) {
        const folder = $(item).parent('.folder'),
            isOpen = folder.hasClass('opened'),
            [iconOpen, iconClose] = ['bx-folder-open', 'bx-folder'],
            [openerOpen, openerClosed] = ['bx-chevron-down', 'bx-chevron-right'];

        let folderIcon = $(item).hasClass('opener') ? $(item).siblings('span').children('i') : $(item).children('i');
        let openerIcon = $(item).parent().children('i');

        if (ch) {
            folderIcon.toggleClass(`${iconOpen} ${iconClose}`);
            openerIcon.toggleClass(`${openerOpen} ${openerClosed}`);
        } else {
            let folderIcon = $('<i>', {class: `bx ${isOpen ? iconOpen : iconClose}`});
            $(item).hasClass('opener') ? $(item).siblings('span').prepend(folderIcon) : $(item).prepend(folderIcon)

            if (folder.data('item') > 0) {
                folder.prepend(`<i class='opener bx ${isOpen ? openerOpen : openerClosed}'></i>`)
            } else {
                folder.css('margin-left', '26px')
            }
        }
    }


    $(document).off('dblclick', opener).on('dblclick', '.files .folder span:not(.file span)', async function () {
        await opener(this)
    });

    $(document).off('dblclick', opener).on('dblclick', '.list-view .folder, .list-view .file', async function () {
        let clicked = $(this);
        localStorage.setItem('data-path', $(clicked).data('path'));
        await placeItems(clicked, true);
        $('.add-options .add-option').prop('disabled', false)
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.list-view .folder, .list-view .file').length) {
            $('.list-view .folder, .list-view .file').removeClass('selected');
        }
    });

    $(document).off('click', opener).on('click', '.files .folder span:not(.file span)', async function () {
        let clicked = $(this).parent('.folder');
        localStorage.setItem('data-path', $(clicked).data('path'));
        await placeItems(clicked, true);
        $('.add-options .add-option').prop('disabled', false)
    });

    $(document).off('click', '.opener').on('click', '.opener', async function () {
        await opener(this);
    });

    $(document).off('click', '.setting-option').on('click', '.setting-option', async function () {
        let clickedOp = $(this);
        axios.post(`nf-file-manager/settings`, {
            key: $(clickedOp).data('key'),
            value: $(clickedOp).data('value'),
        }).then(async function (response) {
            let path = localStorage.getItem('data-path') ?? null,
                data = response.data;

            if (response.status === 200) {
                if (path) await placeItems(null, true, path);
                $(`.${data.key}`).addClass('d-none');
                $(`.${data.key}-${data.value}`).removeClass('d-none');

                $(clickedOp).parents().find('i.check-ico').removeClass('bx-check-circle')
                $(clickedOp).find('i.check-ico').addClass('bx-check-circle')
            }
        })
    });

    $(document).off('click', '.add-option').on('click', '.add-option', async function () {
        let clickedOp = $(this), osb = 'button[data-option="submit"]',
            option = $(clickedOp).data('add');
        let path = localStorage.getItem('data-path');

        const om = new bootstrap.Modal('#option-modal');
        om.show()

        $('#option-modal').find('.modal-title').text(`Add new ${option}`)
        $(osb).text(`Create`)
        $(document).off('click', osb).on('click', osb, function () {
            axios.post(`nf-file-manager/add`, {
                addOp: option,
                path: path,
                name: $(this).parents('.modal-content').find('#item_name').val()
            }).then(async function (response) {
                if (response.status === 200) {
                    om.hide();
                    if (path) await placeItems(null, true, path);
                }
            })
        })
    });

    $(document).off('click', '.nf-option-rename').on('click', '.nf-option-rename', async function () {
        let sf = $('.list-view .folder.selected-nf-operation, .list-view .file.selected-nf-operation').eq(0);
        let ft = $(sf).hasClass('file') ? 'file' : 'folder';
        let fp = $(sf).data('path'), oms = $('#option-modal'), nv = fp.split('\\'),
            osb = 'button[data-option="submit"]';

        let op = localStorage.getItem('data-path');

        const om = new bootstrap.Modal('#option-modal');
        om.show()

        $(oms).find('.modal-title').text(`Rename ${ft}`)
        $(oms).find('#item_name').val(nv[nv.length - 1])
        $(osb).text(`Rename`)
        $(document).off('click', osb).on('click', osb, function () {
            axios.post(`nf-file-manager/rename`, {
                pn: nv[nv.length - 1],
                type: ft,
                path: fp,
                name: $(this).parents('.modal-content').find('#item_name').val()
            }).then(async function (response) {
                if (response.status === 200) {
                    om.hide();
                    if (op) await placeItems(null, true, op);
                }
            })
        })
    });


    let isDragging = false;
    let startX, startY;
    let $selectionBox = $('<div class="selection-drag-box"></div>').appendTo('body').hide();

    $(document).on('mousedown', '.list-view', function (event) {
        if ($(event.target).is('.list-view .file, .list-view .folder')) return;

        console.log(event.target);

        isDragging = true;
        startX = event.pageX;
        startY = event.pageY;

        $selectionBox.css({left: startX, top: startY, width: 0, height: 0});

        if (!event.ctrlKey) {
            $('.list-view .file, .list-view .folder').removeClass('selected-nf-operation');
        }
    });

    $(document).on('mousemove', function (event) {
        if (!isDragging) return;

        let mouseX = event.pageX, mouseY = event.pageY;
        let width = Math.abs(mouseX - startX), height = Math.abs(mouseY - startY);
        let left = Math.min(mouseX, startX), top = Math.min(mouseY, startY);

        $selectionBox.css({width: width, height: height, left: left, top: top}).show();

        $('.list-view .file, .list-view .folder').each(function () {
            let $this = $(this), offset = $this.offset(), el = offset.left, et = offset.top,
                er = el + $this.outerWidth(), eb = et + $this.outerHeight();
            let sl = left, st = top, sr = sl + width, sb = st + height;

            if (er > sl && el < sr && eb > st && et < sb) {
                $this.addClass('selected-nf-operation');
            }
        });
    });

    $(document).on('mouseup', function () {
        if (isDragging) {
            isDragging = false;
            $selectionBox.hide();
        }
        availableOption();
    });


    $(document).off('click', opener).on('click', '.list-view .folder, .list-view .file', function (event) {
        if (!event.ctrlKey) {
            $('.list-view .folder, .list-view .file').removeClass('selected-nf-operation')
            $(this).addClass('selected-nf-operation')
        } else {
            $(this).toggleClass('selected-nf-operation')
        }
        availableOption();
    });

    function availableOption() {
        let sl = $('.list-view .folder.selected-nf-operation, .list-view .file.selected-nf-operation').length;
        let $files = $('.file-options .for-select-item').not('.nf-option-paste');

        if (sl === 1) {
            $files.removeClass('nfp-unable').addClass('nfp-able')
        } else if (sl > 1) {
            $files.removeClass('nfp-unable').addClass('nfp-able')
            $('.file-options .nf-option-rename,.file-options .nf-option-edit').addClass('nfp-unable').removeClass('nfp-able')
        } else {
            $files.removeClass('nfp-able').addClass('nfp-unable')
        }

        if (clipBoard.status === 1) $('.file-options .nf-option-paste').removeClass('nfp-unable').addClass('nfp-able');
    }


    $(document).on('click', '.file-options .nf-option-delete', function () {
        let sf = $('.list-view .folder.selected-nf-operation, .list-view .file.selected-nf-operation');
        let op = [];
        let path = localStorage.getItem('data-path');
        $.each(sf, (i, s) => {
            let tf = $(s).hasClass('file') ? 'file' : 'folder';
            op.push({path: $(s).data('path'), type: tf})
        })

        axios.delete(`nf-file-manager/delete`, {
            params: {path: op,}
        }).then(async function (response) {
            if (response.status === 200) {
                if (path) await placeItems(null, true, path);
            }
        });
    })


    $(document).on('click', '.file-options .nf-option-copy', function () {
        let af = $('.list-view .folder, .list-view .file');
        $(af).removeClass('nf-cut');
        clipboard('copy')
    })

    $(document).on('click', '.file-options .nf-option-cut', function () {
        let af = $('.list-view .folder, .list-view .file');
        $(af).removeClass('nf-cut');
        let sf = $('.list-view .folder.selected-nf-operation, .list-view .file.selected-nf-operation');
        $(sf).addClass('nf-cut');
        clipboard('cut')
    })


    function clipboard(po = null) {
        let sf = $('.list-view .folder.selected-nf-operation, .list-view .file.selected-nf-operation');
        let $files = [];
        $.each(sf, (i, s) => {
            let tf = $(s).hasClass('file') ? 'file' : 'folder';
            $files.push({path: $(s).data('path'), type: tf})
        })

        clipBoard.files = $files;
        clipBoard.type = po;
        clipBoard.status = 1;
        availableOption()
    }

    $(document).on('click', '.file-options .nf-option-paste, button[data-paste]', function () {
        let sf = $('.list-view .folder.selected-nf-operation, .list-view .file.selected-nf-operation');
        let toDi = sf.length === 1 && $(sf).eq(0).hasClass('folder') ? $(sf).eq(0).data('path') : localStorage.getItem('data-path');
        let path = localStorage.getItem('data-path');

        axios.post(`nf-file-manager/rearrange`, {
            to: toDi,
            arrange: $(this).data('paste') ?? null,
            clipboard: clipBoard
        }).then(async function (response) {
            console.log(response.data)
            if (response.data === 'conflict') {
                wm.show()
            } else {
                if (response.status === 200) {
                    if (path) await placeItems(null, true, path);
                }
                console.log(response.data)
                wm.hide();
            }
        });
    })


    function getTypeIconByExt(ext) {
        let $types = {
            'txt': {
                fType: 'Text File',
                icon: 'bxs-file-txt'
            },
            'pdf': {
                fType: 'PDF Document',
                icon: 'bxs-file-pdf'
            },
            'jpg': {
                fType: 'Image',
                icon: 'bxs-file-jpg'
            },
            'png': {
                fType: 'Image',
                icon: 'bxs-file-png'
            },
            'gif': {
                fType: 'Image',
                icon: 'bxs-file-gif'
            },
            'bmp': {
                fType: 'Image',
                icon: 'bxs-file-image'
            },
            'tiff': {
                fType: 'Image',
                icon: 'bxs-file-image'
            },
            'webp': {
                fType: 'Image',
                icon: 'bxs-file-image'
            },
            'iso': {
                fType: 'Image',
                icon: 'bxs-file-image'
            },
            'img': {
                fType: 'Image',
                icon: 'bxs-file-image'
            },
            'jpeg': {
                fType: 'Image',
                icon: 'bxs-file-image'
            },
            'doc': {
                fType: 'Microsoft Word Document',
                icon: 'bxs-file-doc'
            },
            'docx': {
                fType: 'Microsoft Word Document (Open XML)',
                icon: 'bxs-file-doc'
            },
            'xls': {
                fType: 'Microsoft Excel Spreadsheet',
                icon: 'bx-file'
            },
            'xlsx': {
                fType: 'Microsoft Excel Spreadsheet (Open XML)',
                icon: 'bx-file'
            },
            'ppt': {
                fType: 'Microsoft PowerPoint Presentation',
                icon: 'bx-file'
            },
            'pptx': {
                fType: 'Microsoft PowerPoint Presentation (Open XML)',
                icon: 'bx-file'
            },
            'csv': {
                fType: 'Comma-Separated Values (CSV) File',
                icon: 'bx-file'
            },
            'xml': {
                fType: 'XML Document',
                icon: 'bx-code-alt'
            },
            'html': {
                fType: 'HTML Document',
                icon: 'bxs-file-html'
            },
            'php': {
                fType: 'PHP Script',
                icon: 'bxl-php'
            },
            'js': {
                fType: 'JavaScript File',
                icon: 'bxs-file-js'
            },
            'css': {
                fType: 'CSS Stylesheet',
                icon: 'bxs-file-css'
            },
            'json': {
                fType: 'JSON File',
                icon: 'bxs-file-json'
            },
            'zip': {
                fType: 'ZIP Archive',
                icon: 'bxs-file-archive'
            },
            'rar': {
                fType: 'RAR Archive',
                icon: 'bxs-file-archive'
            },
            'tar': {
                fType: 'TAR Archive',
                icon: 'bxs-file-archive'
            },
            'gz': {
                fType: 'Gzip Compressed Archive',
                icon: 'bxs-file-archive'
            },
            '7z': {
                fType: '7-Zip Archive',
                icon: 'bxs-file-archive'
            },
            'mp3': {
                fType: 'MP3 Audio File',
                icon: 'bx-music'
            },
            'wav': {
                fType: 'Waveform Audio File',
                icon: 'bx-music'
            },
            'mp4': {
                fType: 'MP4 Video File',
                icon: 'bx-video'
            },
            'avi': {
                fType: 'AVI Video File',
                icon: 'bx-video'
            },
            'mov': {
                fType: 'QuickTime Movie File',
                icon: 'bx-video'
            },
            'wmv': {
                fType: 'Windows Media Video File',
                icon: 'bx-video'
            },
            'flv': {
                fType: 'Flash Video File',
                icon: 'bx-video'
            },
            'exe': {
                fType: 'Executable File',
                icon: 'bx-file'
            },
            'dll': {
                fType: 'Dynamic Link Library',
                icon: 'bx-file'
            },
            'bat': {
                fType: 'Batch File',
                icon: 'bx-file'
            },
            'sh': {
                fType: 'Shell Script',
                icon: 'bx-code-block'
            },
            'py': {
                fType: 'Python Script',
                icon: 'bxl-python'
            },
            'java': {
                fType: 'Java Source Code File',
                icon: 'bxl-java'
            },
            'c': {
                fType: 'C Source Code File',
                icon: 'bx-code-alt'
            },
            'jar': {
                fType: 'Java Archive File',
                icon: 'bxs-file-archive'
            },
            'sql': {
                fType: 'SQL Script',
                icon: 'bx-data'
            },
            'mdb': {
                fType: 'Microsoft Access Database File',
                icon: 'bx-data'
            },
            'sqlite': {
                fType: 'SQLite Database File',
                icon: 'bx-data'
            },
            'db': {
                fType: 'Database File',
                icon: 'bx-data'
            },
            'dat': {
                fType: 'Data File',
                icon: 'bx-data'
            },
            'log': {
                fType: 'Log File',
                icon: 'bxs-file-txt'
            },
            'ini': {
                fType: 'Configuration File',
                icon: 'bx-file'
            },
            'psd': {
                fType: 'Adobe Photoshop Document',
                icon: 'bx-file'
            },
            'ai': {
                fType: 'Adobe Illustrator File',
                icon: 'bx-file'
            },
            'svg': {
                fType: 'Scalable Vector Graphics File',
                icon: 'bx-file'
            },
            'eps': {
                fType: 'Encapsulated PostScript File',
                icon: 'bx-file'
            },
            'mpg': {
                fType: 'MPEG Video File',
                icon: 'bx-video'
            },
            'mpeg': {
                fType: 'MPEG Video File',
                icon: 'bx-video'
            },
            'ogg': {
                fType: 'OGG Audio File',
                icon: 'bx-music'
            },
            'wma': {
                fType: 'Windows Media Audio File',
                icon: 'bx-music'
            },
            'aac': {
                fType: 'Advanced Audio Coding File',
                icon: 'bx-music'
            },
            'apk': {
                fType: 'Android Package File',
                icon: 'bx-code'
            },
            'deb': {
                fType: 'Debian Package File',
                icon: 'bx-code'
            },
            'yaml': {
                fType: 'YAML Document',
                icon: 'bx-code-alt'
            },
            'yml': {
                fType: 'YAML Document',
                icon: 'bx-code-alt'
            },
            'md': {
                fType: 'Markdown Document',
                icon: 'bxs-file-md'
            },
            'unknown': {
                fType: 'Unknown File',
                icon: 'bx-error-circle'
            }
        };

        if ($types.hasOwnProperty(ext)) {
            return $types[ext];
        }
        return $types['unknown'];
    }
})
