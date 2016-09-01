<?php
    session_start();
    require_once '../../lang/lang_'.$_SESSION['lang'].'.php';
    header("Content-type: text/js");
?>

var app = angular.module('myApp', ['angularFileUpload']);

app.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});

app.controller('RegisterController',function($scope,$http){
    $scope.ok;
    $scope.submit = function () {
        $scope.error = [];
        
        if($scope.form.email && $scope.form.name && $scope.form.password && $scope.form.password2 && $scope.form.address && $scope.form.mobile){
            $scope.ok=true;
            email = /^[a-z0-9\._-]{3,20}@[a-z]{3,15}\.[a-z\.]{2,6}$/i;
            namecheck = /^[a-z\s]{3,40}$/i;
            password = /^[a-z0-9а-яЁё@&!]{8,}$/i;
            numberc = /^0[0-9]{2}\s*-{0,1}\s*[0-9]{3}\s*-{0,1}\s*[0-9]{2}\s*-{0,1}\s*[0-9]{2}$/;
            unvanc = /.{5,}/;
            
            if(!email.test($scope.form.email)){
                $scope.error.push('<?=$language['wrongEmail'] ?>');
                $scope.ok=false;
            }
            
            if(!namecheck.test($scope.form.name)){
               $scope.error.push('<?=$language['wrongName'] ?>');
               $scope.ok=false;
            }
            
            if(!password.test($scope.form.password)){
                $scope.error.push("<?=$language['passwordDChanged'] ?>");
                $scope.ok=false;
            }
            
            if($scope.form.password!==$scope.form.password2){
                $scope.error.push("<?=$language['passwordMatch'] ?>");
                $scope.ok=false;
            }
            if(!numberc.test($scope.form.mobile)){
                 $scope.error.push("<?=$language['wrongMobile'] ?>");
                 $scope.ok=false;
            }
            if(!unvanc.test($scope.form.address)){
                 $scope.error.push("<?=$language['wrongAddress'] ?>");
                 $scope.ok=false;
            }
        }
        else{
            $scope.error.push("<?=$language['fillAll'] ?>");
            $scope.ok=false;
        }
        if($scope.ok){
            $http.post("/qeydiyyat",$scope.form).success(function(data){
                console.log(data);
                if(data.success){
                    location.href = data.success;
                }
                if(data.error){
                    data.error.map(function(value){
                        $scope.error.push(value);
                    });
                }
            });
        }
    }
});

app.controller("CategoryController",function($scope,$http){
    
    $scope.getCats = function(){
        $http.get("/category/get?get=cat").success(function(data){
            $scope.cats = data;
        });
    };

    $scope.goto=function(a){
        location.href = '/panel/category/'+a;
    }
    
    $scope.getSubs = function(){
        $http.get("/category/get?get=sub").success(function(data){
            $scope.subs = data;
        });
    };
    $scope.yoxla = /^./i;
    $scope.addCat = function(category){
        $scope.errorCat=[];

        if($scope.yoxla.test(category.name_az) && $scope.yoxla.test(category.name_en) && $scope.yoxla.test(category.name_ru)){
            $http.post('/panel/category?add=cat',category).success(function(data){
                if (data.success){
                    category.name_az = '';
                    category.name_en = '';
                    category.name_ru = '';
                    
                    $scope.getCats();
                }
                else{
                    $scope.errorCat.push(data.error);
                }
                
            });
        }
        else{
            
            $scope.errorCat.push("Saheleri doldur");
        }
    }
    
    $scope.addSub = function(subcategory){
        $scope.errorSub=[];
        if($scope.yoxla.test(subcategory.name_az) && $scope.yoxla.test(subcategory.name_en) && $scope.yoxla.test(subcategory.name_ru)){
            $http.post('/panel/category?add=sub',subcategory).success(function(data){
                if (data.success){
                    subcategory.name_az = '';
                    subcategory.name_en = '';
                    subcategory.name_ru = '';
                    
                    $scope.getSubs();
                }
                else{
                    $scope.errorSub.push(data.error);
                }
                
            });
        }
        else{
            
            $scope.errorSub.push("Saheleri doldur");
        }
    }
    
    
    $scope.delete= function(obj,what){
        if(confirm("Are you sure?")){
            $http.get("/panel/category?delete="+obj.id).success(function(data){
                if (what=='cat'){
                    index = $scope.cats.indexOf(obj);
                    $scope.cats.splice(index,1);
                }
                else{
                    index = $scope.subs.indexOf(obj);
                    $scope.subs.splice(index,1);
                }
            });
            
        }
        
        
    }
    
    $scope.getCats();
    $scope.getSubs();
    
    
});

app.controller("NewProductController",function($scope,$http,FileUploader){
    
   $scope.images = new FileUploader({url:"/fileupload?id=3",removeAfterUpload:true});
    
   $scope.getSubs = function(){
        $http.get("/category/get?get=sub").success(function(data){
            $scope.subs = data;
        });
    };
    
    
    $scope.images.filters.push({
        name: 'imageFilter',
        fn: function(item, options) {
            var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|gif|'.indexOf(type) !== -1;
        }
    });
    
    $scope.images.onAfterAddingFile(function(fileItem){
        console.log(fileItem);
    });
    
    $scope.images.onCompleteAll(function(){
        location.href='/panel';
    })
    
    $scope.showSelected = function(){
        selected = $scope.subs.filter(function(val){
            return val.selected;
        });
        return selected;
    }
    
    $scope.cansend = true;
    
    $scope.submit = function(product){
        $scope.errors = [];
        if(!$scope.cansend){
            $scope.errors.push("<?=$language['updating'] ?>");
        }
        else
        if((product.name_az!='' ||product.name_ru!=''||product.name_en!='') && (product.text_az!='' ||product.text_ru!=''||product.text_en!='')){
            selected = $scope.showSelected($scope.product);
            if (selected.length>0){
                if($scope.images.queue.length==0){
                    $scope.errors.push('<?=$language['atLeastOneImage'] ?>');
                }
                else{
                    $scope.cansend = false;
                    $http.post("/panel/yeni-mehsul",product).success(function(data){
                    if(data.error){
                        $scope.errors=data.error;
                        $scope.cansend=true;
                        
                    }
                    else{
                        console.log(data);
                        $http.post("/panel/yeni-mehsul?id="+data,selected).success(function(daata){
                            console.log(daata);
alert(daata);
                            $scope.images.uploadAll();
                            
                            timer = setInterval(function(){
                                if($scope.images.queue.length==0){
                                    clearInterval(timer);
                                    location.href="/panel";
                                }
                            },100);
                            
                        });
                    }
                });
                }
                
            }
            else{
                $scope.errors.push("<?=$language['oneCat'] ?>");
            }
            
        }
        else{
            $scope.errors.push("Bezi sahelei doldurmaqi unutmayin");
        }
        
    };
    
    $scope.getSubs();
});

app.controller("PanelController",function($scope,$http){
    $scope.products = [];
    $scope.getProducts = function(){
        $http.get('/myProducts').success(function(data){
            $scope.products = data;
        });
    }
    $scope.sil = function(product){
        if(confirm("Bax deqiq olsun")){
            $http.get('/sil?id='+product.id).success(function(data){
                console.log(data);
                index = $scope.products.indexOf(product);
                $scope.products.splice(index,1);
            });
            
        }
    }
    $scope.getProducts();
})

app.controller("ItemController",function($scope,$http){
    $scope.amount =1;
    $scope.add = function(max){
        console.log(max);
        if($scope.amount < max)
        $scope.amount++;
    }
    $scope.lessen = function(){
        if($scope.amount>1){
            $scope.amount--;
        }
    }
    $scope.id;
    $scope.getImages = function(id){
        $scope.id = id;
        $http.get('/images?id='+id).success(function(data){
            $scope.images = data;   
        });
        $http.get('/wishlist?act=get&id='+id).success(function (data) {
            console.log(id+" "+data);
            if(data==1){
                $scope.inList=data;
            }
            else $scope.inList=false;

        });
    };
    $scope.inList=0;

    $scope.sended = false;
    $scope.wishlist = function(){
        if(!$scope.sended){
            $scope.sended= true;
            $http.get('/wishlist?act=set&id='+$scope.id).success(function (data) {
                $scope.sended=false;
                $scope.inList = !$scope.inList;
            })
        }


    }
    
    $scope.setImage= function(image){
        myImage = document.getElementById("myImage");
        myImage.setAttribute('src','/img/'+image);
    };
});

app.controller("UsersController",function($scope,$http){
    $http.get('/panel/users?act=get').success(function(data){
        $scope.users = data;
    });
    
    $scope.makeIt = function(user){
        if(user.privilege==1){
            $http.get("/panel/users?act=upd&id="+user.id+"&type=2").success(function(data){
                user.privilege=2;
            });
        }
        else {
            $http.get("/panel/users?act=upd&id="+user.id+"&type=1").success(function(data){
                user.privilege=1;
            });
        }
    }
    
    $scope.sil = function(user){
        if(confirm('Deqiq?')){
            $http.get("/panel/users?act=del&id="+user.id).success(function(data){
                index = $scope.users.indexOf(user);
                $scope.users.splice(index,1);
                console.log(data);
            });
            
        }
    }
    
    $scope.getType=function(type){
        if(type==1){
            return "seller";
        }
        else return "buyer";
    };
});





app.controller("UpdateProductController",function($scope,$http,FileUploader){
    
   $scope.images = new FileUploader({url:"/fileupload?id=3",removeAfterUpload:true});
    
   $scope.getImages = function(id){
       $http.get('/images?id='+id).success(function(data){
           filtered = data.map(function(val){
               if(val.name===$scope.product.image.trim()){
                   val.main = true;
               }
               return val;
           });
           $scope.p_images = filtered;
       });       
   };
    $scope.bunuSil = function(image){
        if(confirm("Silmek isteyirsen?")){
            if(image.main){
                alert("Bu shekil esasdir bunu silmek olmaz");
            }
            else{
                $http.get("/image_act?act=del&image="+image.name).success(function(data){
                    console.log("/image_act?act=del&image="+image.name);
                    console.log(data);
                    index = $scope.p_images.indexOf(image);
                    $scope.p_images.splice(index,1);
                });
                
                
            }
        }
    };
    
    $scope.esas = function(image){
        
        $http.get("/image_act?act=upd&image="+image.name).success(function(data){
            $scope.p_images.map(function(val){
                if(val.main){
                    val.main=false;
                }
                return val;
            });
            image.main = true;
        });
        
        
        
        
    }
    
   $scope.getSubs = function(id){
        $http.get("/category/get?get=sub").success(function(data){
            $scope.subs = data;
            $scope.selectedSubs(id);
            $scope.getImages(id);
        });
    };
    
    $scope.selectedSubs = function(id){
        $http.get("/selectedCats?id="+id).success(function(data){
            data.map(function(val){
                $scope.subs = $scope.subs.map(function(value){
                    if(value.id==val.category_id){
                        value.selected = true;
                    }
                    return value;
                        
                });
            });
        });
    };
    
    $scope.bashla = function(data){
        $scope.product = data;
        $scope.product.amount = parseInt(data.amount);
        $scope.product.price = parseInt(data.price);
        $scope.getSubs(data.id);
    };
    
    $scope.images.filters.push({
        name: 'imageFilter',
        fn: function(item, options) {
            var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|gif|'.indexOf(type) !== -1;
        }
    });
    
    $scope.showSelected = function(){
        selected = $scope.subs.filter(function(val){
            return val.selected;
        });
        return selected;
    };
    
    $scope.cansend = true;
    
    $scope.submit = function(product){
        $scope.errors = [];
        if(!$scope.cansend){
            $scope.errors.push("<?=$language['updating'] ?>");
        }
        else
        if((product.name_az!='' ||product.name_ru!=''||product.name_en!='') && (product.text_az!='' ||product.text_ru!=''||product.text_en!='')){
            selected = $scope.showSelected($scope.product);
            if (selected.length>0){
                $scope.cansend = false;
                $scope.errors.push('<?=$language['updating'] ?>');
                product.say = parseInt(product.say);
                product.price = parseInt(product.price);
                $http.post("/panel/edit",product).success(function(data){
                if(data.error){
                    $scope.errors=data.error;
                    $scope.cansend=true;
                }
                else{
                    $http.post("/panel/yeni-mehsul?id="+data,selected).success(function(data){
                        
                        $scope.images.uploadAll();

                        timer = setInterval(function(){
                            console.log($scope.images.queue.length);
                            if($scope.images.queue.length==0){
                                clearInterval(timer);
                                location.href="/panel";
                            }
                        },500);
                        

                    });
                }
            });
                
            }
            else{
                $scope.errors.push("<?=$language['oneCat'] ?>");
            }
            
        }
        else{
                $scope.errors.push("<?=$language['fillSome'] ?>");
        }
        
    };
    
});

app.controller('CommentController',function($scope,$http){
    $http.get('/panel/comment?tema=123').success(function(data){
        $scope.comments = data;
    });

    $scope.sil = function(comment){
        if(confirm("Deqiq Silirsen?")){
            $http.get('/panel/comment?tema=del&id='+comment.id).success(function(){
                $scope.silArray(comment);
            });
        }
    }

    $scope.approve = function(comment){
        if(confirm("Dəqiq Təsdiqləyirən?")){
            $http.get('/panel/comment?tema=tes&id='+comment.id).success(function(data){
                console.log(data);
                $scope.silArray(comment);
            });
        }
    }

    $scope.silArray = function(comment){
        index = $scope.comments.indexOf(comment);
        $scope.comments.splice(index,1);
    }
});

app.controller('ApproveController',function($scope,$http){
    $http.get('/panel/approve?tema=all').success(function(data){
        $scope.products = data;
    });

    $scope.sil = function(product){
        if(confirm("Bax deqiq olsun")){
            $http.get('/sil?id='+product.id).success(function(data){
                console.log(data);
                index = $scope.products.indexOf(product);
                $scope.products.splice(index,1);
            });

        }
    }

    $scope.approve = function(product){
        if(confirm("Bax deqiq olsun")){
            $http.get('/panel/approve?tema=app&id='+product.id).success(function(data){
                console.log(data);
                index = $scope.products.indexOf(product);
                $scope.products.splice(index,1);
            });

        }
    }
});