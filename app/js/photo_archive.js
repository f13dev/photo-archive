jQuery(document).ready(function($) {
    let base_url = $('#pa_script').data('base_url');
    let lightbox_open = false;
    generate();

    $(document).ajaxComplete(function(e, r, o) {
        if (o.url.includes('do=load_folder')) {
            console.log('folder opened, checking generation');
            generate();
        }
    });

    function generate() {
        if ($('.requires_generation').length) {
            var g = $('.requires_generation')[0];
            var container = $(g).parent().parent();
            var container_id = container.attr('id');
            container.addClass('generating');
            container.addClass('generating');
            $.ajax({
                type: 'POST',
                url: base_url,
                data: { ajax: true, do: 'generate_image', gallery: container.data('gallery'), file: container.data('file') },
            }).done(function(d) {
                $(g).remove();
                var urls = JSON.parse(d);
                if (urls && urls.thumb && urls.mid) {
                    container.attr('href', urls.mid);
                    $('#'+container_id+' .item .image').css('background-image', 'url("'+urls.thumb+'")');
                }
                generate();
            }).fail(function(d) {
                console.log(d);
                $(g).remove();
                alert('Failed to generate image');
                generate();
            })
        }
    }

    $(window).on("popstate", function (e) {
        location.reload();
    });

    $(document).keyup(function(event) {
        if (lightbox_open) {                                // Lightbox navigation
            if (event.keyCode === 39) {                     // Right arrow
                $('#lightbox-next').click();
            } else
            if (event.keyCode === 37) {                     // Left arrow
                $('#lightbox-prev').click();
            } else
            if (event.keyCode === 27) {                     // Escape
                $('#lightbox-close').click();
            } else 
            if (event.keyCode == 84 && event.altKey) {      // Alt + T
                if ($('#edit_tags').length) {
                    $('#edit_tags').click();
                }
            } else 
            if (event.keyCode == 65 && event.altKey) {      // Alt + A 
                event.preventDefault;
                if ($('#edit_description').length) {
                    $('#edit_description').click();
                }
            } else 
            if (event.keyCode == 40) {                      // Down
                if ($('#add_tags_suggest div').length) {
                    var val = $('#add_tags_input').val();
                    var text = false;
                    var select = false;
                    $('#add_tags_suggest div').each(function() {
                        $(this).css('background', 'unset');
                        if (select == true) {
                            text = $(this).text();
                            $(this).css('background', '#ccc');
                            select = false;
                        }
                        if (val == $(this).text()) {
                            select = true
                        }
                    });
                    if (!text) {
                        text = $('#add_tags_suggest div').first().text();
                        $('#add_tags_suggest div').first().css('background', '#ccc');
                    }
                    if (text) {
                        $('#add_tags_input').val(text);
                    }
                }
            } else  
            if (event.keyCode == 38) {                      // Up

            }
        }
    });

    $(document).on('click', '.resync_close', function(e) {
        e.preventDefault();
        $('#resync_popup').remove();
    });

    $(document).on('click', '.resync', function(e) {
        e.preventDefault();
        var resync = '<div id="resync_popup"><span class="resync_close">X</span><iframe class="resync_container" src="'+$(this).data('href')+'"></iframe></div>';
        $('body').append(resync);
    });

    $(document).on('click', '.ajax-link', function(e) {
        e.preventDefault();
        var href = $(this).data('href');
        var target = $(this).data('target');
        var term = $(this).data('term');
        var tags = $(this).data('tags');
        var folder = $(this).data('folder');
        if ($(this).data('folder')) {
            $('#viewing').html('Viewing: '+unescape($(this).data('folder').replace(/\+/g, " ")));
        } else
        if (term) {
            $('#viewing').html('Search results: '+unescape(term));
        } else 
        if (tags) {
            $('#viewing').html('Tags');
        }
        $(target+' *').css('transition', 'opacity 0.3s').css('opacity', '0'); //.html(''); //.css('opacity', '0.5');//.css('display', 'none');
        $('#lightbox-loading').css('display', 'block');
        $.ajax({
            url: href,
            cache: false,
        }).done(function(output) {
            if (tags) {
                window.history.pushState('Tags', 'Tags', base_url+'tags');
            } else 
            if (term) {
                window.history.pushState('Search', 'Search', base_url+'search/'+term.replace(/ /g, '+'));
                console.log(term);
            } else {
                window.history.pushState('Folder', 'Folder', base_url+'dir'+decodeURIComponent(folder));
            }

            $(target).html(output).css('opacity', '1');

            var loaded = 0;
            var total = $('.gallery-image').length;

            if (total == 0) {
                check_loaded();
            }

            function check_loaded() {
                if (loaded == total) {
                    $('#lightbox-loading').css('display', 'none');
                    $(target).css('display', 'block');
                }
            }

            $(".gallery-image")
                .on('load', function() { loaded++; check_loaded();})
                .on('error', function() { loaded++; check_loaded();})
        }).fail(function(e, t) {
            console.log('Error', e, t);
        });
    });

    $(document).on('click', '.ajax-image', function(e) {
        e.preventDefault();
        var file = $(this).data('file');
        var folder = $(this).data('folder');
        var exif = $(this).data('exif');
        var href = $(this).attr('href');
        var number = $(this).data('number');
        var total = $(this).data('total');
        var ext = $(this).data('ext');
        var db_id = $(this).data('db-id');
        var description = $(this).data('description');
        var tags = $(this).data('tags');
        var raw = $(this).data('raw');
        var lightbox_nav = $(this).data('lightbox_nav');
        var elem = '';
        var img = false;

        lightbox_open = true;

        $('#lightbox').css('display', 'block');
        $('#lightbox-loading').css('display', 'none');

        ext = href.substr( (href.lastIndexOf('.') +1) ).toLowerCase();

        if (ext == 'jpg' || ext == 'png' || ext == 'gif') {
            elem = '<img src="'+href+'" id="lightbox-image">';
            img = href;
        } else 
        if (ext == 'mp4' || ext == 'mov') {
            elem = '<video controls class="lightbox-image"><source src="'+href+'" type="video/mp4"></video>';
        } else {
            elem = '<span class="lightbox-image">Unsupported file, please re-sync the gallery to convert this file.</span>';
        }

        if (img) {
            $('#lightbox-container').css('background-image', 'url("'+img+'")');
        } else {
            $('#lightbox-container').css('background-image', 'unset');
        }

        $('#lightbox-content').html(elem);
        if (lightbox_nav) {         // Next/Prev fade out then fade in
        //    $('#lightbox-content').fadeOut(function() {
        //        $('#lightbox-content').html(elem).fadeIn();
        //    });
        } else {                    // New lightbox, just fade in
        //    $('#lightbox-content').hide().html(elem).fadeIn('fast');
        }

        $('#lightbox-caption-text').html(exif);
        $('#lightbox-caption-description').html(atob(description)); 
        
        var tag_decode = atob(tags);
        tag_decode = JSON.parse(tag_decode);
        tag_decode = JSON.parse(tag_decode);
        var tag_string = '';
        $.each(tag_decode, function(i, tag) {
            var elem = '<span class="tag">'+tag.tag+'</span>';
            tag_string = tag_string+elem;
        });
        $('#lightbox-caption-tags').html(tag_string);

        var next = number + 1;
        var prev = number - 1;
        $('#lightbox-next').data('folder', $('#image-'+next).data('folder')).data('gallery', $('#image-'+next).data('folder')).data('exif', $('#image-'+next).data('exif')).attr('href', $('#image-'+next).attr('href')).data('number', $('#image-'+next).data('number')).data('total', $('#image-'+next).data('total')).data('db-id', $('#image-'+next).data('db-id')).data('description', $('#image-'+next).data('description')).data('tags', $('#image-'+next).data('tags')).data('file', $('#image-'+next).data('file')).data('raw', $('#image-'+next).data('raw'));
        $('#lightbox-prev').data('folder', $('#image-'+prev).data('folder')).data('gallery', $('#image-'+prev).data('folder')).data('exif', $('#image-'+prev).data('exif')).attr('href', $('#image-'+prev).attr('href')).data('number', $('#image-'+prev).data('number')).data('total', $('#image-'+prev).data('total')).data('db-id', $('#image-'+prev).data('db-id')).data('description', $('#image-'+prev).data('description')).data('tags', $('#image-'+prev).data('tags')).data('file', $('#image-'+prev).data('file')).data('raw', $('#image-'+prev).data('raw'));
        $('#edit_description').data('db-id', db_id).data('folder-name', folder).data('file-name', file).data('number', number);  
        $('#edit_tags').data('db-id', db_id).data('folder-name', folder).data('file-name', file).data('number', number);    
        
        $('#lightbox-download-link').attr('href', href.replace(mid_url, image_url).replace(thumb_url, image_url));
        $('#lightbox-download-raw-link').attr('href', raw);
        if (raw == '') {
            $('#lightbox-download-raw-link').hide();
        } else {
            $('#lightbox-download-raw-link').show();
        }

        if (number == total) {
            $('#lightbox-next').css('opacity', '0.5');
        } else {
            $('#lightbox-next').css('opacity', '1');
        }
        if (number == 1) {
            $('#lightbox-prev').css('opacity', '0.5');
        } else {
            $('#lightbox-prev').css('opacity', '1');
        }

        function loaded() {
            $('#lightbox-loading').css('display', 'none');
            $('#lightbox-content img').css('display', 'block');
        }

        $("#lightbox-content img").on('load', function() { 
            loaded();
        }).on('error', function() { 
            loaded();
        });
    });

    $(document).on('click', '#lightbox-close', function() {
        lightbox_open = false;
        $('#lightbox').css('display', 'none');
        $('#lightbox-content').html('');
    });

    $(document).on('click', '#edit_description', function() {
        var ajax_url = $(this).data('ajax-url');
        var file = $(this).data('file-name');
        var folder = $(this).data('folder-name');
        var number = $(this).data('number');

        var ajax_url = ajax_url+'file='+encodeURIComponent(file)+'&folder='+encodeURIComponent(folder)+'&number='+encodeURIComponent(number);
    
        $.ajax({
            url: ajax_url,
            cache: false,
        }).done(function(output) {
            $('#lightbox-caption-description').html(output);
            $('#about_input').focus();
        }).fail(function(d) {
            alert('Ajax loading failed');
        });
    });

    $(document).on('click', '#edit_tags', function() {
        var ajax_url = $(this).data('ajax-url');
        var file = $(this).data('file-name');
        var folder = $(this).data('folder-name');
        var number = $(this).data('number');

        var ajax_url = ajax_url+'file='+encodeURIComponent(file)+'&folder='+encodeURIComponent(folder)+'&number='+encodeURIComponent(number);
        
        $.ajax({
            url: ajax_url,
            cache: false,
        }).done(function(output) {
            $('#lightbox-caption-tags').html(output).focus();
            $('#add_tags_input').focus();
        }).fail(function(d) {
            alert('Ajax loading failed');
        });
    });

    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        var method = $(this).attr('method');

        var submit = $(this).children('input[type="submit"]');
        submit.css('display', 'none');    

        var id = $(this).attr('id');

        if (id == 'edit_description_form') {
            var description = $(this).children('textarea[name="description"]').val();
            var number = $(this).children('input[name="number"]').val();
        } else 
        if (id == 'edit_tags_form') {
            $('#add_tags_input').trigger('add_tag');

            var values = $("input[name='tags[]']")
            .map(function(){return $(this).val();}).get(); 
            
            let obj = [];
            values.forEach(function(value, key) {
                var eachTag = {'tag' : value};
                obj.push(eachTag);
            });

            values = JSON.stringify(obj)
            var number = $(this).children('input[name="number"]').val();
        }

        var formData = new FormData(this);
        var url = $(this).data('url');

        $.ajax({
            type : method,
            url : url,
            data : formData,
            processData: false,
            contentType: false,
        }).done(function(d) {
            $(target).html(d);
            submit.css('display', 'unset');
            // Update the description on the loaded image data
            if (id == 'edit_description_form') {
                var elem = '#image-'+number;
                $(elem).data('description', btoa(description));
            } else 
            if (id == 'edit_tags_form') {
                var elem = '#image-'+number;
                $(elem).data('tags', btoa(JSON.stringify(values)));
            }
        }).fail(function(d) {
            alert('An error occurred.');
        });
    });

    // Start of tags
    $(document).on('add_tag', '#add_tags input', function() {
        var txt = this.value.replace(/[^a-zA-Z0-9\s\+\-\.\#]/ig,''); // allowed characters
        if(txt) $("<span/>", {html:txt+'<input type="hidden" name="tags[]" value="'+txt+'">', insertBefore:this});
        this.value = "";
        $('#add_tags_input').focus();
    });

    $(document).on('add_tag', 'input[name="search_term"]', function() {
        var txt = this.value.replace(/[^a-zA-Z0-9\s\+\-\.\#]/ig,''); // allowed characters
        if (txt) $("<span/>", {html:txt+'<input type="hidden" name="tags[]" value="'+txt+'">', insertBefore:this});
        this.value = "";
        $(this).focus();
    })

    $(document).on('keyup', 'input[name="search_term"]', function(ev) {
        var suggest = $('#search_term_suggest');
        if (/(188|13)/.test(ev.which)) {
            $(this).trigger("add_tag");
        } else 
        if (this.value != '') {
            var ajax = $(this).data('ajax');
            var url = ajax+'do=suggest_tag&text='+encodeURIComponent(this.value);
            $.ajax({
                type: 'GET',
                url: url,
            }).done(function(d) {
                $(suggest).html(d).css('display', 'block');
            }).fail(function(d) {
                alert('An error ocurred.');
            });
        } else {
            $(suggest).html('').css('display', 'none');
        }
    });

    $(document).on('keyup', '#add_tags_input', function(ev) {
        if (ev.keyCode == 40 || ev.keyCode == 38) {
            return;
        }
        if(/(188|13)/.test(ev.which)) {
            $(this).trigger("add_tag"); 
        } else 
        var suggest = $('#add_tags_suggest');
        if (this.value != '') {
            var ajax = $(this).data('ajax');
            var url = ajax+'do=suggest_tag&text='+encodeURIComponent(this.value);
            // Ajax call to get tag suggestions

            $.ajax({
                type : 'GET',
                url : url,
            }).done(function(d) {
                $(suggest).html(d).css('display', 'block');
            }).fail(function(d) {
                alert('An error ocurred.');
            });
        } else {
            $(suggest).html('').css('display', 'none');
        }
    });

    $(document).on('click', '#search_term_suggest div', function() {
        var input = $('input[name="search_term"]');
        var tag = $(this).html();
        input.val(tag);
        input.trigger('add_tag');
        $('#search_term_suggest').html('').hide();
    })

    $(document).on('click', '#add_tags_suggest div', function() {
        var input = $('#add_tags_input');
        var tag = $(this).html();
        input.val(tag);
        input.trigger('add_tag');
        $('#add_tags_suggest').html('').hide();
    });

    $(document).on('click', '#add_tags span', function() {
        if(confirm("Remove "+ $(this).text() +"?")) $(this).remove(); 
    });

    $(document).on('click', '#search span', function() {
        $(this).remove();
    });

    $(document).on('submit', '#search, .search', function(e) {
        e.preventDefault();
        var url = $(this).data('href');
        var term = $(this).children('input[name="search_term"]').val();
        var method = $(this).attr('method');
        var formData = new FormData(this);

        $('#lightbox-loading').css('display', 'block');

        var terms = $('input[name^=tags]').map(function(idx, elem) {
            return $(elem).val();
          }).get();

          var term = '';
          var url_term = '';
          terms.forEach(function(value, key) {
              if (term == '') {
                  term = value;
                  url_term = value.replace(/ /g, '+');
              } else {
                  term = term+', '+value;
                  url_term = url_term+','+value.replace(/ /g, '+');
              }
          });

        if (term != '') {
            $.ajax({
                type : method,
                url : url,
                data : formData,
                processData: false,
                contentType: false,
            }).done(function(d) {
                $('#container').html(d);
                $('#viewing').html('Search results: '+unescape(term));
                $('#search').slideToggle('slow');
                $('#lightbox-loading').css('display', 'none');
                window.history.pushState('Search', 'Search', base_url+'search/'+url_term);
            }).fail(function(d) {
                alert('An error ocurred.');
                $('#lightbox-loading').css('display', 'none');
            });
        }
    });

    $(document).on('click', '#search-toggle', function() {
        $('#search').slideToggle('slow');
        $('input[name="search_term"]').focus();
    });
});

jQuery.fn.extend({
    live: function (event, callback) {
       if (this.selector) {
            jQuery(document).on(event, this.selector, callback);
        }
        return this;
    }
});