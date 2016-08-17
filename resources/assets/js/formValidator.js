function validateForm(rules){
    jQuery.each(rules, function(key, val) {
        var rules = val.split("|");
        jQuery.each(rules, function(i, rule){
            addRule(key,rule);
        });
    });
}

function addRule(field, rule){
    var params = rule.split(":");
    rule = params[0];
    //console.log("Add rule " + rule + " to field " + field);
    if      (rule == 'required') addRuleRequired(field);
    else if (rule == 'email')    addRuleEmail   (field);
    else if (rule == 'numeric')  addRuleNumeric (field);
    else if (rule == 'numeric')  addRuleInteger (field);
    else if (rule == 'min')      addRuleMin     (field, params[1]);
    else if (rule == 'url')      addRuleURL     (field);
    else if (rule == 'ip')       addRuleIP      (field);
    else if (rule == 'domain')   addRuleDomain  (field);
}

function addRuleRequired(field){
    $("#"+field).prop('required',true);
}

function addRuleEmail(field){
    $("#"+field).attr('type','email');
}

function addRuleNumeric(field){
    $("#"+field).attr('type','number')
                .attr('step','any');
}

function addRuleInteger(field){
    $("#"+field).attr('type','number');
}

function addRuleMin(field,min){
    $("#"+field).attr('pattern','.{' + min + ',}')
                .attr('title', min + ' Or more characters');
}

function addRuleURL(field){
    $("#"+field).attr('pattern','https?://.+')
                .attr('title', 'Needs to be a webpage');
}

function addRuleIP(field){
    $("#"+field).attr('pattern','((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4}$')
                .attr('title', 'Needs to be an IP');
}

function addRuleDomain(field){
    $("#"+field).attr('pattern','^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$')
                .attr('title', 'Needs to be an IP');
}