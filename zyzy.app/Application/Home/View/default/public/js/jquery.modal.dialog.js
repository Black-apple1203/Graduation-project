/* ============================================================
 * jquery.modal.dialog.js  弹框Plugin
 * ============================================================
 * Copyright 74cms.
 * ============================================================ */
+function($) {
    'use strict';

    var Dialog = function(element, options) {
        this.options = this.getOptions(options);
        this.$body = $(document.body);
        this.$element = $(element);
        this.$backdrop = null;
        this.$modal = null;
        this.$dialog = null;
        this.$dialogBox = null;
        this.$dialogHeader = null;
        this.$dialogTitle = null;
        this.$dialogContent = null;
        this.$dialogFooter = null;
        this.$dismissModal = null;
        this.$yesOperation = null;
        this.$otherOperation = null;
        this.$cancelOperation = null;
        this.$closeDiloag = true;
        this.$ie = !-[1, ];
        this.$ie6 = !-[1, ] && !window.XMLHttpRequest;
        this.show();
    }

    Dialog.DEFAULTS = {
        backdrop: true,
        border: true,
        template: ['<div class="modal">', '<div class="modal_dialog">', '<div class="modal_content pie_about">', '<div class="modal_header">', '<span class="title modal_title"></span>', '<span class="max_remind"></span>', '<a href="javascript:;" class="close J_dismiss_modal_close"></a>', '</div>', '<div class="modal_body"></div>', '<div class="modal_footer">', '<div class="res_add_but">', '<div class="butlist">', '<input type="button" readonly="readonly" class="btn_blue J_hoverbut btn_100_38 J_dismiss_modal_yes J_btnyes" value="" />', '</div>', '<div class="butlist">', '<input type="button" readonly="readonly" class="btn_lightgray J_hoverbut btn_100_38 J_dismiss_modal J_btncancel" value="" />', '</div>', '<div class="clear"></div>', '</div>', '</div>', '<input type="hidden" class="J_btnload" />', '</div>', '</div>', '</div>'].join(''),
        template3: ['<div class="modal">', '<div class="modal_dialog">', '<div class="modal_content pie_about">', '<div class="modal_header">', '<span class="title modal_title"></span>', '<span class="max_remind"></span>', '<a href="javascript:;" class="close J_dismiss_modal_close"></a>', '</div>', '<div class="modal_body"></div>', '<div class="modal_footer">', '<div class="res_add_but b3">', '<div class="butlist">', '<input type="button" readonly="readonly" class="btn_blue J_hoverbut btn_100_38 J_dismiss_modal_yes J_btnyes" value="" />', '</div>', '<div class="butlist">', '<input type="button" readonly="readonly" class="btn_blue J_hoverbut btn_100_38 J_dismiss_modal J_btnother" value="" />', '</div>', '<div class="butlist">', '<input type="button" readonly="readonly" class="btn_lightgray J_hoverbut btn_100_38 J_dismiss_modal J_btncancel" value="" />', '</div>', '<div class="clear"></div>', '</div>', '</div>', '<input type="hidden" class="J_btnload" />', '</div>', '</div>', '</div>'].join(''),
        header: true,
        title: '',
        content: '',
        loading: false,
        footer: true,
        showFooter: true,
        showClose: true,
        innerPadding: true,
        btns: ['确定', '取消'],
        btnOne: false,
        nobgColor: false,
        loadFun: function() {},
        yes: function() {},
        other: function() {},
        cancel: function() {}
    }

    Dialog.prototype.init = function() {
        if (eval(this.options.btnNum) > 2) {
            this.$modal = $(this.options.template3).appendTo(this.$body);
        } else {
            this.$modal = $(this.options.template).appendTo(this.$body);
        }
        this.$dialog = this.$modal.find('.modal_dialog');
        this.$dialogBox = this.$modal.find('.modal_content');
        this.$dialogHeader = this.$modal.find('.modal_header');
        this.$dialogTitle = this.$modal.find('.modal_title');
        this.$dialogContent = this.$modal.find('.modal_body');
        this.$dialogFooter = this.$modal.find('.modal_footer');
        this.$dismissModal = this.$modal.find('.J_dismiss_modal');
        this.$dismissModalClose = this.$modal.find('.J_dismiss_modal_close');
        this.$dismissModalYes = this.$modal.find('.J_dismiss_modal_yes');
        this.$btnOperation = this.$modal.find('.res_add_but');
        this.$yesOperation = this.$modal.find('.J_btnyes');
        this.$close = this.$modal.find('.close');
        if (eval(this.options.btnNum) > 2) {
            this.$otherOperation = this.$modal.find('.J_btnother');
        }
        this.$cancelOperation = this.$modal.find('.J_btncancel');
        if (!this.options.innerPadding) {
            this.$dialogContent.addClass('no_pad');
        }
        if (!this.options.border) {
            this.$dialogBox.addClass('no_pad');
        }
        if (this.options.nobgColor) {
            this.$dialogBox.css('background', 'none');
        }
        if (this.options.header) {
            this.setTitle(this.options.title);
        } else {
            this.$dialogHeader.remove();
        }
        if (this.options.loading) {
            this.setContent('<div class="ajax_loading"><div class="ajaxloadtxt"></div></div>');
        } else {
            this.setContent(this.options.content);
        }
        if (this.options.footer) {
            this.setBtns(this.options.btns);
        } else {
            this.$dialogFooter.remove();
        }
        if (this.options.showFooter) {
            this.$dialogFooter.show();
        } else {
            this.$dialogFooter.hide();
        }
        if (this.options.showClose) {
            this.$close.show();
        } else {
            this.$close.hide();
        }
        this.operation();
        this.btnHover();
        this.move();
        //this.escape();
    }

    Dialog.prototype.show = function() {
        var that = this;
        this.backdrop();
        this.init();
        this.options.loadFun();
        this.$dismissModal.on('click', function() {
            that.hide();
        })
        this.$dismissModalClose.on('click', function() {
            that.$modal.remove();
            if (that.$backdrop) {
                that.$backdrop.remove();
            }
        })
        this.$dismissModalYes.on('click', function() {
            if (that.$closeDiloag) {
                that.hide();
            }
        })
    }

    Dialog.prototype.setCloseDialog = function(switchVal) {
        eval(switchVal) ? this.$closeDiloag = true : this.$closeDiloag = false;
    }

    Dialog.prototype.hide = function() {
        // this.options.cancel();
        this.$modal.remove();
        if (this.$backdrop) {
            this.$backdrop.remove();
        }
    }

    Dialog.prototype.escape = function() {
        var that = this;
        $(document).on('keydown', function(event) {
            if (event.keyCode == 27) {
                that.hide();
            }
        })
    }

    Dialog.prototype.hideDialog = function() {
        this.$modal.remove();
    }

    Dialog.prototype.move = function() {
        var newObj = this.$dialog;
        var newTit = this.$dialogHeader;
        newTit.mousedown(function(e) {
            var offset = newObj.offset();
            var x = e.pageX - offset.left;
            var y = e.pageY - offset.top;
            $(document).bind('mousemove', function(ev) {
                newObj.bind('selectstart', function() {
                    return false;
                });
                var newx = ev.pageX - x;
                var newy = ev.pageY - y;
                newObj.css({
                    'left': newx + "px",
                    'top': newy + "px"
                })
            })
        })
        $(document).mouseup(function() {
            $(this).unbind("mousemove");
        })
    }

    Dialog.prototype.btnHover = function() {
        $(".J_hoverbut").hover(function() {
            $(this).addClass("hover");
        }, function() {
            $(this).removeClass("hover");
        })
    }

    Dialog.prototype.getDefaults = function() {
        return Dialog.DEFAULTS;
    }

    Dialog.prototype.getOptions = function(options) {
        options = $.extend({}, this.getDefaults(), options);
        return options;
    }

    Dialog.prototype.backdrop = function() {
        var that = this;
        if (this.options.backdrop) {
            this.$backdrop = $(document.createElement('div')).addClass('modal_backdrop fade').appendTo(this.$body);
        }
    }

    Dialog.prototype.setTitle = function(title) {
        this.$dialogTitle.html(title);
    }

    Dialog.prototype.setContent = function(content) {
        this.$dialogContent.html(content);
        this.setPosition();
        $('.modal_dialog').css({
            left: ($(window).width() - $('.modal_dialog').outerWidth()) / 2,
            top: ($(window).height() - $('.modal_dialog').outerHeight()) / 2 + $(document).scrollTop()
        })
    }

    Dialog.prototype.showFooter = function(switchVal) {
        if (eval(switchVal)) {
            this.$dialogFooter.show();
        } else {
            this.$dialogFooter.hide();
        }
    }

    Dialog.prototype.setInnerPadding = function(switchVal) {
        if (eval(switchVal)) {
            this.$dialogContent.removeClass('no_pad');
        } else {
            this.$dialogContent.addClass('no_pad');
        }
    }

    Dialog.prototype.setBtns = function(btnArr) {
        if (this.options.btnOne) {
            this.$btnOperation.addClass('btn-one');
            this.$cancelOperation.closest('.butlist').remove();
            this.$yesOperation.val(btnArr[0]);
        } else {
            if (eval(this.options.btnNum) > 2) {
                this.$yesOperation.val(btnArr[0]);
                this.$otherOperation.val(btnArr[1]);
                this.$cancelOperation.val(btnArr[2]);
            } else {
                this.$yesOperation.val(btnArr[0]);
                this.$cancelOperation.val(btnArr[1]);
            }
        }
    }

    Dialog.prototype.setBtnClass = function(classArr) {
        this.$yesOperation.removeClass('btn_100_38').addClass(classArr[0]);
        this.$cancelOperation.removeClass('btn_100_38').addClass(classArr[1]);
    }

    Dialog.prototype.operation = function(content) {
        this.$yesOperation.click(this.options.yes);
        if (eval(this.options.btnNum) > 2) {
            this.$otherOperation.click(this.options.other);
        }
        this.$cancelOperation.click(this.options.cancel);
    }

    Dialog.prototype.setPosition = function() {
        var that = this;
        this.$dialog.css({
            left: ($(window).width() - this.$dialog.outerWidth()) / 2,
            top: ($(window).height() - this.$dialog.outerHeight()) / 2 + $(document).scrollTop()
        })
        // 处理ie7下头部、底部不能自适应宽度
        // function isIe() {
        //     return ("ActiveXObject"in window);
        // }
        // if (isIe()) {
        //     this.$dialog.css('width', this.$dialogContent.find('div').eq(0).width() + 40);
        // }
        if (this.$ie) {
            if (window.PIE) {
                $('.pie_about').each(function() {
                    PIE.attach(this);
                });
            }
        }
        if (this.options.backdrop) {
            this.$backdrop.addClass('in');
        }
    }

    function Plugin(option) {
        return new Dialog(this,option);
    }

    $.fn.dialog = Plugin;
    $.fn.dialog.Constructor = Dialog;

    $.fn.dialog.noConflict = function() {
        $.fn.dialog = old;
        return this
    }

}(jQuery);
