var SelfTest = {};  //namespace

SelfTest.dataUrl = "/selfTestResult";

SelfTest.ajax = {
    get: function(url) {
        var q = $.Deferred();
        
        $.get(url)
            .done(function(data){
                q.resolve(data);
            })
            .fail(function(data){
                q.reject(data);
            });
        
        return q;
    },
    
    post: function(url, data) {
        var q = $.Deferred();
        
        $.post(url, data)
            .done(function(data){
                q.resolve(data);
            })
            .fail(function(data){
                q.reject(data);
            });
        
        return q;
    }
};

$(document).ready(function(){
    
    testData.forEach(function(testGroup){
        
        testGroup.actions.forEach(function(actionElem){
            var actionId = actionElem.id;
            var $row = $("tr#" + actionId);
            
            SelfTest.ajax.get(SelfTest.dataUrl + '?plugin=' + actionElem.plugin + '&name=' + actionElem.name).then(
                function(data){
                    if (data.success) {
                        $row.find("td.result-cell").html('<span class="glyphicon glyphicon-ok" style="color:green"></span>');
                    } else {
                        $row.find("td.result-cell").html('<span class="glyphicon glyphicon-remove" style="color:red"></span>');
                    }
                },
                function() {
                    $row.find("td.result-cell").html('<span class="glyphicon glyphicon-remove" style="color:red"></span>');
                }
            );
        });
    });
});
