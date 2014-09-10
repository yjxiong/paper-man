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
    self.getPaperInfoURI = self.apiBase+'getPaperByID';
    self.editPaperInfoURI = self.apiBase+'editPaper';
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
                           status: "Hide",
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

    self.editPaperDialog = function(paper){
        paper_id = paper.id

        $.ajax({
            url: self.getPaperInfoURI,
            type: 'GET',
            data: {
                paper_id: paper_id
            },
            success: function(data){
                if ((data.error_code != 1000 )||( data.content.length== 0)) {
                    console.log("can't get paper info");
                }else{
//                    console.log(data);
                    // Push the data to the table
                    paper = data.content;
                    delete paper.paper_id;
                    editPaperView.showEditDiaglog(paper, paper_id)

                }
            },
            error: function(data){
                alert('Connection error!');
            }
        });

    }

    self.changePaperVis = function(paper){

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

    self.deletePaperDialog = function(paper){
        bootbox.dialog({
            title: "Confirm deleting",
            message: "You are deleting paper <b>"+paper.title()+"</b>.\n Once deleted, it will be removed from database. You need to insert it again if you want it to be displayed. \n Are you SURE?",
            buttons: {
                delete: {
                    label: "Confirm and Delete",
                    className: "btn-danger",
                    callback: function() {
                        self.deletePaper(paper.id)
                    }
                },
                cancel: {
                    label: "Cancel",
                    className: "btn-default",
                    callback: function() {

                    }
                }
            }
        });
    }

    self.deletePaper = function(paper_id){

    }

    self.editPaper = function(paper, paper_id){

        delete paper.paper_id;

        $.ajax({
            url: self.editPaperInfoURI,
            type: 'POST',
            data: {
                paper_id: paper_id,
                updated_paper: paper
            },
            dataType: 'json',
            success: function(data){
                if (data.error_code != 1000) {
                    alert('Backend error '+data.error_code+": "+data.error_msg);
                } else self.refreshPaper();
            },
            error: function(data){
                alert('Connection error!');
            }
        });
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
    self.editFields = ko.observableArray();
    self.current_paper = null;
    self.current_paper_id = null;


    self.confirm = function(){

        if ((self.current_paper == null)||(self.current_paper_id== null)){
            self.cleanup();
            console.error('Error call to edit paper');
            return;
        }

        ko.utils.arrayForEach(self.editFields(), function(field){
            self.current_paper[field.label.toLowerCase()] = field.content;
        })

        paperview.editPaper(self.current_paper, self.current_paper_id);

        self.cleanup();
    }

    self.cancel = function(){
        self.cleanup();
    }

    self.cleanup = function(){
        self.current_paper = null;
        self.current_paper_id = null;
        $('#pm-edit-paper-dialog').modal('hide');
    }

    self.showEditDiaglog = function(paper, paper_id){
//        keys = Object.keys(paper);
//        console.log(keys);
//        console.log(paper)
        self.current_paper = paper;
        self.current_paper_id = paper_id;
        self.editFields.removeAll();
        for (var prop in paper){

            if (prop == 'paper_id'){
                continue;
            }
            if (paper.hasOwnProperty(prop)){
                if (paper[prop]){
                    self.editFields.push({
                        label: prop.toUpperCase(),
                        content: paper[prop],
                        holder: prop
                    });
                }else{

                }

            }

        }

        $("#pm-edit-paper-dialog").modal('show')
    }
}

$(function() {
    paperMan.init();
});

var paperview = new paperMan.paperViewModel();
var addPaperView = new paperMan.addPaperViewModel();
var editPaperView = new paperMan.editPaperViewModel();
ko.applyBindings(paperview, $('#paper_list')[0]);
ko.applyBindings(addPaperView, $('#pm-add-paper-dialog')[0]);
ko.applyBindings(editPaperView, $('#pm-edit-paper-dialog')[0]);