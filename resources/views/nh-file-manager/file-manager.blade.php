<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link rel="stylesheet" href="{{ asset('assets/labs/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/labs/boxicons/css/boxicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/scss/style.css') }}">
</head>
<body>
<div id="app">
    <div class="left-side side-menu bg-primary">
        <div class="app-logo">
            <img class="logo" src="assets/images/logo.png" alt="logo">
        </div>
        <div class="app-menu">
            <a href="" class="menu-item active">
                <i class='bx bx-folder'></i>
                Files
            </a>
        </div>
    </div>
    <div class="right-side file-manager bg-dark">
        <div class="header">
            <div class="menu-item">
                <h3>Files</h3>
            </div>
            <div class="right-items">
                <div class="search menu-item">
                    <input type="text" placeholder="File name" aria-label="Search">
                    <i class="ti-search search-icon"></i>
                </div>
                <div class="menu-item">
                    <i class="ti-settings"></i>
                </div>
                <div class="btn-group menu-item profile-menu">
                    <button type="button" class="d-flex dropdown-toggle align-items-center profile-menu-button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <p class="m-0 me-2">Admin Saheb</p>
                        <img class="profile-image rounded" src="assets/images/profile.jpg" alt="">
                    </button>
                    <ul class="dropdown-menu">
                        <li class="menu-profile">
                            <div class="cover">
                                <img class="cover-image" src="assets/images/cover.jpg" alt="cover">
                            </div>
                            <div class="profile">
                                <img class="profile-image rounded" src="assets/images/profile.jpg" alt="profile">
                                <p>Luca Manish</p>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger fw-bold" href="#">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container-fluid application">
            <div class="row">
                <div class="col-md-3 p-0 border-end file-menu-bar">
                    <div class="d-flex align-items-center justify-content-between option-bar px-3">
                        <h6 class="card-title">My Folders</h6>
                    </div>
                    <hr>
                    <div class="files-menu px-3">
                        <ul class="files">
                            <li class="folder opened" data-path="{{ $settings['base'] }}" data-item="{{ $items }}"
                                data-load="true">
                                <span>{{ $settings['base'] }}</span>
                                <ul class="folder-items">
                                    @foreach($contains as $file)
                                        @if($file['type'] === 'folder')
                                            <li class="folder" data-path="{{ $file['path'] }}"
                                                data-item="{{ $file['items'] }}">
                                                <span>{{ $file['name'] }}</span>
                                            </li>
                                        @elseif($file['type'] === 'file')
                                            <li class="file" data-ext="{{ $file['ext'] }}">
                                                <span>{{ $file['name'] }}</span></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-9 files-list">
                    <div class="card border-0">
                        <div class="d-flex align-items-center justify-content-between option-bar">
                            <div class="file-options d-flex">
                                <h6 class="option card-title text-capitalize m-0 list-view-opened-folder">{{ $settings['base'] }}</h6>
                                <button class="option nf-option-back nfp-unable">
                                    <i class='bx bx-chevron-left'></i></button>
                                <button class="option nf-option-forward nfp-unable">
                                    <i class='bx bx-chevron-right'></i></button>
                            </div>
                            <div class="file-options d-flex">
                                <a href="" class="option nf-option-cut nfp-able"><i
                                        class='bx bx-cloud-upload'></i>Upload</a>
                                <div class="dropdown">
                                    <button type="button"
                                            class="option dropdown-toggle border-start border-end nfp-able"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i
                                            class='bx bx-plus-circle'></i>Add
                                    </button>
                                    <ul class="dropdown-menu add-options nfp-unable" disabled>
                                        <li>
                                            <button class="dropdown-item option add-option" data-add="folder">
                                                <i class='bx bx-folder'></i>New Folder
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item option add-option" data-add="file"><i
                                                    class='bx bx-file-blank'></i>New File
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                                <button class="option for-select-item nf-option-cut nfp-unable" data-bs-toggle="tooltip"
                                        data-bs-title="Cut"><i class='bx bx-cut'></i></button>
                                <button class="option for-select-item nf-option-copy nfp-unable"
                                        data-bs-toggle="tooltip"
                                        data-bs-title="Copy"><i class='bx bx-copy'></i></button>
                                <button class="option for-select-item nf-option-paste nfp-unable"
                                        data-bs-toggle="tooltip"
                                        data-bs-title="Paste"><i class='bx bx-paste'></i></button>
                                <button class="option for-select-item nf-option-delete nfp-unable"
                                        data-bs-toggle="tooltip"
                                        data-bs-title="Delete"><i class='bx bx-trash'></i></button>
                                <button class="option for-select-item nf-option-rename nfp-unable"
                                        data-bs-toggle="tooltip"
                                        data-bs-title="Rename"><i class='bx bx-rename'></i></button>
                                <button class="option for-select-item nf-option-edit nfp-unable"
                                        data-bs-toggle="tooltip"
                                        data-bs-title="Edit"><i class='bx bx-edit'></i></button>
                                <div class="dropdown">
                                    <button type="button" class="option dropdown-toggle border-start nfp-able"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i
                                            class='bx bx-desktop'></i>View
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item option setting-option" data-key="view"
                                               data-value="grid">
                                                <span><i class='bx bx-layer'></i>Icon</span>
                                                <i @class(['bx check-ico','bx-check-circle' => ($settings['view'] === 'grid')])></i></a>
                                        </li>
                                        <li><a class="dropdown-item option setting-option" data-key="view"
                                               data-value="list">
                                                <span><i class='bx bx-list-ul'></i>List</span>
                                                <i @class(['bx check-ico','bx-check-circle' => ($settings['view'] === 'list')])></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="list-view">
                        <table @class(['table view list-table view-list','d-none' => ($settings['view'] !== 'list')])>
                            <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Type</th>
                                <th scope="col">Size</th>
                            </tr>
                            </thead>
                            <tbody id="list-view-table"></tbody>
                        </table>
                        <div
                            @class(['list-grid view view-grid','d-none' => ($settings['view'] !== 'grid')]) id="list-view-grid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="option-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"></h1>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" name="item_name" id="item_name">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" data-option="submit">Create</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="arrange-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center"><i class='bx bx-error-circle fs-3'></i> Paste Error
                </h5>
            </div>
            <div class="modal-body">
                <p>Some files already exist in the target folder. Do you want to replace it?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" data-paste="new">Paste As New</button>
                <button type="button" class="btn btn-primary btn-sm" data-paste="replace">Yes! Replace</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/labs/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('assets/labs/axios/dist/axios.min.js') }}"></script>
<script src="{{ asset('assets/labs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>
</body>

</html>
