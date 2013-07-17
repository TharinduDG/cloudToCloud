var FileActions = {
    actions:{},
    defaults:{},
    icons:{},
    currentFile:null,
    register:function (mime, name, permissions, icon, action) {
        if (!FileActions.actions[mime]) {
            FileActions.actions[mime] = {};
        }
        if (!FileActions.actions[mime][name]) {
            FileActions.actions[mime][name] = {};
        }
        FileActions.actions[mime][name]['action'] = action;
        FileActions.actions[mime][name]['permissions'] = permissions;
        FileActions.icons[name] = icon;
    },
    setDefault:function (mime, name) {
        FileActions.defaults[mime] = name;
    },
    get:function (mime, type, permissions) {
        var actions = {};
        if (FileActions.actions.all) {
            actions = $.extend(actions, FileActions.actions.all);
        }
        if (mime) {
            if (FileActions.actions[mime]) {
                actions = $.extend(actions, FileActions.actions[mime]);
            }
            var mimePart = mime.substr(0, mime.indexOf('/'));
            if (FileActions.actions[mimePart]) {
                actions = $.extend(actions, FileActions.actions[mimePart]);
            }
        }
        if (type) {//type is 'dir' or 'file'
            if (FileActions.actions[type]) {
                actions = $.extend(actions, FileActions.actions[type]);
            }
        }
        var filteredActions = {};
        $.each(actions, function (name, action) {
            if (action.permissions & permissions) {
                filteredActions[name] = action.action;
            }
        });
        return filteredActions;
    },
    getDefault:function (mime, type, permissions) {
        if (mime) {
            var mimePart = mime.substr(0, mime.indexOf('/'));
        }
        var name = false;
        if (mime && FileActions.defaults[mime]) {
            name = FileActions.defaults[mime];
        } else if (mime && FileActions.defaults[mimePart]) {
            name = FileActions.defaults[mimePart];
        } else if (type && FileActions.defaults[type]) {
            name = FileActions.defaults[type];
        } else {
            name = FileActions.defaults.all;
        }
        var actions = this.get(mime, type, permissions);
        return actions[name];
    },
    display:function (parent) {
        FileActions.currentFile = parent;
        var actions = FileActions.get(FileActions.getCurrentMimeType(), FileActions.getCurrentType(), FileActions.getCurrentPermissions());
        var file = FileActions.getCurrentFile();
        if ($('tr').filterAttr('data-file', file).data('renaming')) {
            return;
        }
        parent.children('a.name').append('<span class="fileactions" />');
        var defaultAction = FileActions.getDefault(FileActions.getCurrentMimeType(), FileActions.getCurrentType(), FileActions.getCurrentPermissions());

        var actionHandler = function (event) {
            event.stopPropagation();
            event.preventDefault();

            FileActions.currentFile = event.data.elem;
            var filename = FileActions.getCurrentFile();

            // event.data.actionFunc(file);

            if ($('#dir').val() == '/') {
                var item = $('#dir').val() + filename;
            } else {
                var item = $('#dir').val() + '/' + filename;
            }
            var tr = $('tr').filterAttr('data-file', filename);
            if ($(tr).data('type') == 'dir') {
                var itemType = 'folder';
            } else {
                var itemType = 'file';
            }
            var possiblePermissions = $(tr).data('permissions');
            var appendTo = $(tr).find('td.filename');
            // Check if drop down is already visible for a different file
            if (OC.Share.droppedDown) {
                if ($(tr).data('id') != $('#dropdown').attr('data-item-source')) {
                    OC.Share.hideDropDown(function () {
                        $(tr).addClass('mouseOver');
                        showDropdown(itemType, $(tr).data('id'), appendTo, true, possiblePermissions, filename);
                    });
                } else {
                    OC.Share.hideDropDown();
                }
            } else {
                $(tr).addClass('mouseOver');
                showDropdown(itemType, $(tr).data('id'), appendTo, true, possiblePermissions, filename);
            }

        };

        var showDropdown = function (itemType, itemSource, appendTo, link, possiblePermissions, file) {

            var data = OC.Share.loadItem(itemType, itemSource);
            var html = '<div id="dropdown" class="drop" data-item-type="' + itemType + '" data-item-source="' + itemSource + '">';

            if (possiblePermissions & OC.PERMISSION_SHARE) {
                html += '<p>Share With</p><input id="shareWith" type="text" placeholder="' + 'user@www.mycloud.com' + '" />';
                html += '<br/><input id="shared_email" type="text" placeholder="' + 'email' + '" />';
                html += '<br/><button id="c2cShare" >Share</button>';
                $(html).appendTo(appendTo);

            } else {
                html += '<input id="shareWith" type="text" placeholder="' + t('core', 'Resharing is not allowed') + '" style="width:90%;" disabled="disabled"/>';
                html += '</div>';
                $(html).appendTo(appendTo);
            }
            $('#dropdown').show('blind', function () {
                OC.Share.droppedDown = true;
            });

            $("#dropdown").parent().on('click', '#shareWith', function () {
                $("#shareWith").removeClass('error');
            });

            $("#dropdown").parent().on('click', '#shared_email', function () {
                $("#shared_email").removeClass('error');
            });

            $("#dropdown").parent().on('click', '#c2cShare', function (e) {
                var link = $("#shareWith").val().split('@');
                var email = $("#shared_email").val();
                var dataType = $("#dropdown").parent().parent().attr('data-type');
                var fileId = $("#dropdown").parent().parent().attr('data-id');
                var user;
                var host;

                var validated = true;
                if (link.length == 2) {
                    user = link[0];
                    host = 'http://' + link[1];
                    var regex_url = new RegExp("^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)*((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(\:[0-9]+)*(/($|[a-zA-Z0-9\.\,\?\'\\\+&amp;%\$#\=~_\-]+))*$");
                    var regex_email = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

                    if (!(regex_email.test(email) && !$("#shared_email").hasClass('error'))) {
                        $("#shared_email").addClass('error');
                        validated = false;
                    }

                    if (!(regex_url.test(host) && !$("#shareWithl").hasClass('error'))) {
                        $("#shareWith").addClass('error');
                        validated = false;
                    }

                    if (validated) {
                        $.ajax({
                            type:"POST",
                            url:OC.filePath('cloud_to_cloud', 'ajax', 'c2cShare.php'),
                            data:{username:user, url:host, filename:file, email_id:email, type: dataType, dataId : fileId},
                            dataType:"json",
                            success:function (result) {
                                if(result['data'] == "success"){
                                    alert("Sharing Completed");
                                }else{
                                    alert('File Could not be Shared');
                                }

                            }
                        });
                    }
                } else {
                    validated = false;

                    if($("#shared_email").val() == ""){
                        if (!$("#shared_email").hasClass('error')) {
                            $("#shared_email").addClass('error');
                            validated = false;
                        }
                    }

                    if (!$("#shareWith").hasClass('error')) {
                        $("#shareWith").addClass('error');
                        validated = false;
                    }
                }
                e.stopPropagation();
                if(validated){
                    OC.Share.hideDropDown();
                }
            });

            $('#shareWith').focus();
        };

        var c2cShare = function () {


        };

        var addAction = function (name, action) {
            // NOTE: Temporary fix to prevent rename action in root of Shared directory
            if (name === 'Rename' && $('#dir').val() === '/Shared') {
                return true;
            }

            if ((name === 'Download' || action !== defaultAction) && name !== 'Delete') {
                var img = FileActions.icons[name];
                if (img.call) {
                    img = img(file);
                }
                var html = '<a href="#" class="action" data-action="' + name + '">';
                if (img) {
                    html += '<img class ="svg" src="' + img + '" /> ';
                }

                if (name == 'Share') {
                    html += t('files', name) + '</a>';
                }

                var element = $(html);
                element.data('action', name);
                //alert(element);
                element.on('click', {a:null, elem:parent, actionFunc:actions[name]}, actionHandler);
                parent.find('a.name>span.fileactions').append(element);
            }

        };

//		$.each(actions, function (name, action) {
//			if (name !== 'Share') {
//				//addAction(name, action);
//			}
//		});
        if (actions.Share) {
            addAction('Share', actions.Share);
        }

        if (actions['Delete']) {
//			var img = FileActions.icons['Delete'];
//			if (img.call) {
//				img = img(file);
//			}
//			if (typeof trashBinApp !== 'undefined' && trashBinApp) {
//				var html = '<a href="#" original-title="' + t('files', 'Delete permanently') + '" class="action delete" />';
//			} else {
//				var html = '<a href="#" original-title="' + t('files', 'Delete') + '" class="action delete" />';
//			}
//			var element = $(html);
//			if (img) {
//				element.append($('<img class ="svg" src="' + img + '"/>'));
//			}
//			element.data('action', actions['Delete']);
//			element.on('click', {a: null, elem: parent, actionFunc: actions['Delete']}, actionHandler);
//			parent.parent().children().last().append(element);
        }
    },
    getCurrentFile:function () {
        return FileActions.currentFile.parent().attr('data-file');
    },
    getCurrentMimeType:function () {
        return FileActions.currentFile.parent().attr('data-mime');
    },
    getCurrentType:function () {
        return FileActions.currentFile.parent().attr('data-type');
    },
    getCurrentPermissions:function () {
        return FileActions.currentFile.parent().data('permissions');
    }
};

$(document).ready(function () {
    if ($('#allowZipDownload').val() == 1) {
        var downloadScope = 'all';
    } else {
        var downloadScope = 'file';
    }

    if (typeof disableDownloadActions == 'undefined' || !disableDownloadActions) {
        FileActions.register(downloadScope, 'Download', OC.PERMISSION_READ, function () {
            return OC.imagePath('core', 'actions/download');
        }, function (filename) {
            window.location = OC.filePath('files', 'ajax', 'download.php') + '?files=' + encodeURIComponent(filename) + '&dir=' + encodeURIComponent($('#dir').val());
        });
    }

    $('#fileList tr').each(function () {
        FileActions.display($(this).children('td.filename'));
    });

});

FileActions.register('all', 'Delete', OC.PERMISSION_DELETE, function () {
    return OC.imagePath('core', 'actions/delete');
}, function (filename) {
    if (Files.cancelUpload(filename)) {
        if (filename.substr) {
            filename = [filename];
        }
        $.each(filename, function (index, file) {
            var filename = $('tr').filterAttr('data-file', file);
            filename.hide();
            filename.find('input[type="checkbox"]').removeAttr('checked');
            filename.removeClass('selected');
        });
        procesSelection();
    } else {
        FileList.do_delete(filename);
    }
    $('.tipsy').remove();
});

// t('files', 'Rename')
FileActions.register('all', 'Rename', OC.PERMISSION_UPDATE, function () {
    return OC.imagePath('core', 'actions/rename');
}, function (filename) {
    FileList.rename(filename);
});


FileActions.register('dir', 'Open', OC.PERMISSION_READ, '', function (filename) {
    window.location = OC.linkTo('files', 'index.php') + '?dir=' + encodeURIComponent($('#dir').val()).replace(/%2F/g, '/') + '/' + encodeURIComponent(filename);
});

FileActions.setDefault('dir', 'Open');
