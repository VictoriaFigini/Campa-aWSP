
    function cambiar_validados(){
        document.getElementById("cant_val").innerHTML = "-";
    }


    
    function cambiar_invalidos(){
        document.getElementById("cant_inv").innerHTML = "-";
    }


    function show_loading(){
        
            $("#loadMe").modal({
            backdrop: "static", //remove ability to close modal with click
            keyboard: false, //remove option to close with keyboard
            show: true //Display loader!
          });
          setTimeout(function() {
            $("#loadMe").modal('hide');
          }, 50000);

    }

    function hide_loading(){
        $("#loadMe").modal('hide');
    }
    function getOutput() { 
        let validos = document.getElementById("cant_val").innerHTML;
        let invalidos = document.getElementById("cant_inv").innerHTML;
        let cliente = $('#empresa-cliente').val();
        var nrosession = document.getElementById('nro_session').innerHTML;
        let parametros = {Val: validos, Inv: invalidos, Cliente: cliente, Session: nrosession};
        
        if (cliente == ""){
            alert('Debe seleccionar el nombre de la empresa a mostrar');
        }else if (validos == '-' && invalidos == '-'){
            alert('Su campaña no tiene teléfonos seleccionados')
        } else {
            show_loading();
            $.ajax({ url:'http://localhost/campaign/generararchivo.php?Val='+validos+'&Inv='+invalidos+'&Cliente='+cliente+'&Session='+nrosession, 
                //data: parametros,
                error: function () { 
                    alert('ERROR: verifique los datos ingresados');
                }
        }).done(function(response) { 
            console.log(response);
                    if (response['success'] == 1){                         
                        alert('Debe seleccionar el nombre de la empresa a mostrar');
                    }else {
                        //alert('Campaña descargada');
                        document.querySelector("#descargar").click();
                        let targetURL = 'http://localhost/campaign/index.html';
                        let newURL = document.createElement('a');
                        newURL.href = targetURL;
                        document.body.appendChild(newURL);
                        newURL.click();   
                        hide_loading();  
                    }
            }
            )}
    }


    function download_validos() { 
        if (parseInt(document.getElementById("cant_val").innerHTML) > 0){
            var nrosession = document.getElementById('nro_session').innerHTML;
            show_loading();
            $.ajax({ url:'http://localhost/campaign/downloadcheck.php?Val=Validado&Session='+nrosession, 
                    type: "POST",    
                    error: function () { 
                        alert('ERROR: verifique los datos ingresados');
                    }
                }).done(function(response){
                    console.log(response);
                                          
                        if (response == 1){                            
                            alert('No hay teléfonos válidos');
                        }else if (response == 2){
                            alert(response);
                            alert('Teléfonos válidos descargados '+nrosession+' '+response['success']);
                            document.querySelector("#descargarval").click();
                        }else{
                            alert('No hay teléfonos validos para descargar');
                        }
                        hide_loading(); 
                }); 
            } 
    }

    function download_invalidos() { 
        if (parseInt(document.getElementById("cant_inv").innerHTML) > 0){
            // alert(parseInt(document.getElementById("cant_inv").innerHTML));
            var nrosession = document.getElementById('nro_session').innerHTML;
            show_loading();
            $.ajax({ url:'http://localhost/campaign/downloadcheck.php?Val=Invalido&Session='+nrosession, 
                    //parametros: {Val: "Invalido", Session: nrosession},
                    error: function () { 
                        alert('ERROR: verifique los datos ingresados');
                    }
                }).done(function(response){
                    console.log(response);
                    if (response == 1){
                                
                        alert('No hay teléfonos inválidos');
                    }else if (response ==2){
                        console.log(JSON.stringify(response));
                        alert('Teléfonos invalidos descargados'+nrosession);
                        document.querySelector("#descargarinv").click();
                    } else{
                        console.log(cant);
                        alert('No hay teléfonos inválidos para descargar ');
                    }
                    hide_loading(); 
            });
        }
    }

    function download_para_chequear() { 
        if(parseInt(document.getElementById("cant_chequear").innerHTML) > 0){
            var nrosession = document.getElementById('nro_session').innerHTML;
            show_loading();
            $.ajax({ url:'http://localhost/campaign/downloadcheck.php?Val=para_chequear&Session='+nrosession, 
                    //parametros: {Val: "para_chequear", Session: 16},
                    error: function () { 
                        alert('ERROR: verifique los datos ingresados');
                    }
                }).done(function(response){
                    console.log(response);
                    if(response == 1){  
                        alert('No hay teléfonos para chequear');
                    }else if (response == 2){
                            alert('Teléfonos para chequear descargados'+nrosession);
                            document.querySelector("#descargarchequear").click();
                    }else{
                        alert('No se ejecuta');
                    }
                    hide_loading(); 
                });
        }
    }

    function recargar(){
            var nrosession = document.getElementById('nro_session').innerHTML;
            show_loading();
            $.ajax({
                url: "http://localhost/campaign/recargar.php?Session="+ nrosession,
                type: 'GET'
        }).done(function(response){
                var content = response;
                console.log(content);
                var content2 = content.slice(1,-1);
                var arraycontent = content2.split(',');
                var respuesta = arraycontent[0];
                var respuesta1 = arraycontent[1];
                var respuesta2 = arraycontent[2];
                console.log(respuesta);
                console.log(respuesta1);
                console.log(respuesta2);
                if (respuesta == '"-"'){
                    $("#cant_chequear").html("-");
                } else{
                    $("#cant_chequear").html(respuesta);
                }
                if (respuesta1 == '"-"'){
                    cambiar_validados();
                } else{
                    $("#cant_val").html(respuesta1);
                }
                if (respuesta2 == '"-"'){
                    cambiar_invalidos();
                } else{
                    $("#cant_inv").html(respuesta2);
                }
                hide_loading(); 
            });
    }


