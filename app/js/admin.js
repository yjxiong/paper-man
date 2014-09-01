/**
 * Created by Xiong Yuanjun on 14-8-30.
 */

var paperMan = paperMan || {};


paperMan.init = function(){

    $('#pm-login-button').click(paperMan.adminLogin);
    $('#pm-logout').click(paperMan.adminLogout);


    $.getJSON('../php/paper_man/request.php?action=init', function(data){
        if ((data.content.user=='')||(data.content.user==null)){
            paperMan.hideAdminPanel();
            $('#pm-admin-login').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            })
        }else{


            paperview.listPaper();
            paperMan.createAdminPanel();

        }
    })

}

paperMan.createAdminPanel = function(){
    $("#paper_list").show();
    $("#pm-logout-panel").show();
}

paperMan.hideAdminPanel = function(){
    $("#paper_list").hide();
    $("#pm-logout-panel").hide();
}


paperMan.adminLogin = function(){

    $.ajax({
        url: '../php/paper_man/request.php?action=adminLogin',
        type: 'POST',
        data: {
            username: $('#pm-login-username').val(),
            password: $('#pm-login-password').val()
        },
        dataType: 'json',
        success: function(data){
            if (data.error_code==1000){
                $('#pm-admin-login').modal('hide');
                paperview.listPaper();
                paperMan.createAdminPanel();
            }else{
                $("#pm-login-error").show();
            }
        },
        error: function(data){
            alert('Connection error!');
        }

    });
}

paperMan.adminLogout = function(){
    $.ajax({
        url: '../php/paper_man/request.php?action=adminLogout',
        type: 'GET',
        success: function(data){
            if (data.error_code==1000){
                paperMan.init();
            }else{
                $("#pm-login-error").show();
            }
        },
        error: function(data){
            alert('Connection error!');
        }

    });
    paperview.clearList();
}

paperMan.paperViewModel = function(){
    var self = this;
    self.apiBase = '../php/paper_man/request.php?action='
    self.listPaperURI = self.apiBase+'getAllPapers';
    self.addPaperURI = self.apiBase+'addPaper';
    self.parseBibURI = self.apiBase+'parseBibtex';
    self.papers = ko.observableArray();

    self.listPaper = function(){

        $.ajax({
           url: self.listPaperURI,
           type: 'GET',
           success: function(data){
               if(data.error_code==2000){
                   paperMan.init();
               }else{
                   self.papers.removeAll();
                   for (var i = 0; i<data.content.number;i++){
                       self.papers.push({
                           year: ko.observable(data.content.papers[i].year),
                           author: ko.observable(data.content.papers[i].author),
                           title: ko.observable(data.content.papers[i].title),
                           arena: ko.observable(data.content.papers[i].arena),
                           id: data.content.papers[i].paper_id

                       })
                   }
               }
           }
        });



    }

    self.refreshPaper = function(){
        self.listPaper()
    }

    self.addNewPaperDialog = function(){
        $('#pm-add-paper-dialog').modal('show');
    }

    self.addNewPaper = function (bibtex_str){
        $.ajax({
            url: self.addPaperURI,
            type: 'POST',
            data: {
                bibtex: bibtex_str
            },
            dataType: 'json',
            success: function(data){
                if (data.error_code != 2000) {
                    self.refreshPaper();
                } else paperMan.init();
            },
            error: function(data){
                alert('Connection error!');
            }
        });
    }

    self.deletePaper = function(paper){
        console.log(paper.id);
    }

    self.editPaper = function(paper){
        console.log(paper.id);
    }

    self.clearList = function(){
        self.papers.removeAll();
    }
}

paperMan.addPaperViewModel = function(){
    var self = this;
    self.bibtex = ko.observable();

    self.preparePaper = function(id){
        self.fields.push({label: ko.observable('test'),
            cpntent: ko.observable('t_content')
        });
    }

    self.confirm = function(){
        $('#pm-add-paper-dialog').modal('hide');
        paperview.addNewPaper(self.bibtex());

    }
}


paperMan.editPaperViewModel = function(){
    var self = this;
    self.fields = ko.observablearArray("");

    self.confirm = function(){
        $('#pm-add-paper-dialog').modal('hide');
        paperview.addNewPaper(self.bibtex());

    }
}

$(function() {
    paperMan.init();
});

var paperview = new paperMan.paperViewModel();
var addPaperView = new paperMan.addPaperViewModel();
ko.applyBindings(paperview, $('#paper_list')[0]);
ko.applyBindings(addPaperView, $('#pm-add-paper-dialog')[0]);