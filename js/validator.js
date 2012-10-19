/**
 * Weegbo Validator class file.
 *
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @package scripts
 * @copyright Copyright &copy; 2008-2010 Inspirativ
 * @license http://weegbo.com/license/
 * @since 0.8
 */

/**
 * Add indexOf method in IE
 */
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(elt /*, from*/) {
        var len = this.length;
        var from = Number(arguments[1]) || 0;
        from = (from < 0) ? Math.ceil(from) : Math.floor(from);
        if (from < 0)
            from += len;
        for (; from < len; from++) {
            if (from in this &&
                this[from] === elt)
            return from;
        }
        return -1;
    };
}

var Validator = function(options, regexp) {
    var self  = this;

    this.regexp = regexp;
    this.result = true;
    this.rules = [];
    this.errors = [];
    this.values = [];
    this.elements = [];
    this.defaults = {
        form: null,
        errorLine: true,
        errorBox: null,
        errorClass: null,
        beforeValidate: null,
        afterValidate: null
    };

    $.extend(self.defaults, options);
    $(options.form).submit(function() {
        return self.validate();
    });

    this.rule = function(element, rule, error, key) {
        self.addElement(element, {type: 'rule', rule: rule, error: error, key: key});
        return self;
    };

    this.min = function(element, min, error, key) {
        self.addElement(element, {type: 'min', min: min, error: error, key: key});
        return self;
    };

    this.max = function(element, max, error, key) {
        self.addElement(element, {type: 'max', max: max, error: error, key: key});
        return self;
    };

    this.minmax = function(element, min, max, error, key) {
        self.addElement(element, {type: 'minmax', min: min, max: max, error: error, key: key});
        return self;
    };

    this.minlen = function(element, min, error, key) {
        self.addElement(element, {type: 'minlen', min: min, error: error, key: key});
        return self;
    };

    this.maxlen = function(element, max, error, key) {
        self.addElement(element, {type: 'maxlen', max: max, error: error, key: key});
        return self;
    };

    this.minmaxlen = function(element, min, max, error, key) {
        self.addElement(element, {type: 'minmaxlen', min: min, max: max, error: error, key: key});
        return self;
    };

    this.checked = function(element, error, key) {
        self.addElement(element, {type: 'checked', error: error, key: key});
        return self;
    };

    this.compare = function(element1, element2, error, key) {
        self.addElement(element1, {type: 'compare', element: element2, error: error, key: key});
        return self;
    };

    this.addElement = function(element, rule) {
        rule.key   = (typeof(rule.key) == "undefined") ? null : rule.key;
        rule.error = (typeof(rule.error) == "undefined") ? null : rule.error;
        var index  = self.elements.indexOf(element);

        // Add new rule for this element
        if (index != -1) {
            self.rules[index].push(rule);
        }
        // Create new element and add rule
        else {
            index = self.elements.push(element);
            index -= 1;
            self.rules[index] = new Array();
            self.rules[index].push(rule);
        }
    };

    this.addRule = function(name, value) {
        self.regexp[name] = value;
        return self;    
    };
    
    this.getValues = function() {
        for (var i = 0; i < self.elements.length; i++) {
            self.values[i] = self.getValue(self.elements[i]);
        }
    };

    this.getValue = function(element) {
        var tmp = $(self.defaults.form + ' '  + element);
        var value = null;
        if (tmp.length > 0) {
            tmp = tmp[0];
            var tag = $(tmp).get(0).tagName.toLowerCase();
            switch (tag) {
                case 'select':
                    value = $(tmp).find('option:selected').val();
                    break;
                default:
                    switch ($(tmp).attr('type').toLowerCase()) {
                        case 'checkbox':
                            value = $(tmp).attr('checked');
                            break;
                        default:
                            value = $(tmp).val();
                            break;
                    }
                    break;
            }
        }
        return value;
    };

    this.validate = function() {
        if ('function' === typeof self.defaults.beforeValidate) {
            self.defaults.beforeValidate();
        }
        self.getValues();
        for (var i = 0; i < self.rules.length; i++) {
            for (var j = 0; j < self.rules[i].length; j++) {
                if (self.errors[i] == undefined) {
                    switch(self.rules[i][j].type) {
                        case 'rule':
                            if (self.regexp[self.rules[i][j].rule]) {
                                var regexp = new RegExp(self.regexp[self.rules[i][j].rule], 'ig');
                                if (false == regexp.test(self.values[i])) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                            }
                            break;
                        case 'min':
                                self.values[i] = parseFloat(self.values[i]);
                                self.values[i] = isNaN(self.values[i]) ? 0 : self.values[i];
                                if (self.values[i] <= self.rules[i][j].min) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                                break;
                            break;
                        case 'max':
                                self.values[i] = parseFloat(self.values[i]);
                                self.values[i] = isNaN(self.values[i]) ? 0 : self.values[i];
                                if (self.values[i] > self.rules[i][j].max) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                                break;
                            break;
                        case 'minmax':
                                self.values[i] = parseFloat(self.values[i]);
                                self.values[i] = isNaN(self.values[i]) ? 0 : self.values[i];
                                if (self.values[i] <= self.rules[i][j].min || self.values[i] > self.rules[i][j].max) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                                break;
                            break;
                        case 'minlen':
                                if (self.values[i].length <= self.rules[i][j].min) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                                break;
                            break;
                        case 'maxlen':
                                if (self.values[i].length > self.rules[i][j].max) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                                break;
                            break;
                        case 'minmaxlen':
                                if (self.values[i].length <= self.rules[i][j].min || self.values[i].length > self.rules[i][j].max) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                                break;
                            break;
                        case 'checked':
                            if (self.values[i] == false) {
                                self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                            }
                            break;
                        case 'compare':
                            var value = self.getValue(self.rules[i][j].element);
                            if (value != '' &&  self.values[i] != '') {
                                if (self.values[i] != value) {
                                    self.setError(i, self.rules[i][j].error, self.rules[i][j].key);
                                }
                            }
                            break;
                    }
                }
            }
        }

        var result = self.result;
        if (result == false) {
            self.showErrors();
        }
        if ('function' === typeof self.defaults.afterValidate) {
            self.defaults.afterValidate();
        }
        return result;
    };

    this.setError = function(index, error, key) {
        self.errors[index] = {'error':error, 'key': key};
        self.result = false;
    };

    this.resetErrors = function() {
        if (self.defaults.errorClass != null) {
            $(self.defaults.form + ' .' + self.defaults.errorClass).removeClass(self.defaults.errorClass);
        }
        if (self.defaults.errorLine == true) {
            $(self.defaults.errorBox).html('').hide();
        }
        else {
            for (var i = 0; i < self.rules.length; i++) {
                for (var j = 0; j < self.rules[i].length; j++) {
                    if (self.rules[i][j].key != null) {
                        $(self.defaults.form + ' ' + self.rules[i][j].key).html('').hide();
                    }
                }
            }
        }
    };

    this.showErrors = function() {
        self.resetErrors();
        if (self.defaults.errorLine == true) {
            var error = '';
            for (var i = 0; i < self.errors.length; i++) {
                if (self.errors[i] != undefined) {
                    if (self.defaults.errorClass != null) {
                        $(self.defaults.form + ' ' + self.elements[i]).addClass(self.defaults.errorClass);
                    }
                    error += self.errors[i].error;
                }
            }
            $(self.defaults.errorBox).html(error).show();
        }
        else {
            for (var i = 0; i < self.errors.length; i++) {
                if (self.errors[i] != undefined) {
                    if (self.defaults.errorClass != null) {
                        $(self.defaults.form + ' ' + self.elements[i]).addClass(self.defaults.errorClass);
                    }
                    if (self.errors[i].key != null && self.errors[i].error != null) {
                        $(self.defaults.form + ' ' + self.errors[i].key).html(self.errors[i].error).show();
                    }
                }
            }
        }
        self.errors = [];
        self.values = [];
        self.result = true;
    };
}