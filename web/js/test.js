var CalcAutolist;
var Calculator;
var CalcWriter;
var ItemHepler;

window.addEventListener('load', function(){
    initCalculator();
    $('#calculator_choose_model').on('focus', function(){
        $(this).css('color', '#000').val('');
    }).on('blur', function(){
        $(this).css('color', 'rgb(167, 167, 167)').val('начните вводить цифры модели');
    });
    $('#btn_getCalculationResults').on('click', function(){
        Calculator.showCalculationResults();
        CalcWriter.scrollToResultsContainer();
    });
    $('#calculation-reset-button').on('click', function(){
        Calculator.reset();
    });
    $('#calculation-add-to-cart').on('click', function(){
        $(this).attr('order_target', CalcWriter.orderContent.id);
        CalcWriter.orderContent.createContentHtml( Calculator.items );
        addToCart(this);
    });
});

function initCalculator(){
    Calculator = new Calculator();
    CalcWriter = new CalcWriter();
    ItemHepler = new ItemHepler();
    CalcAutolist = new Autolist({
        brandId: function(){ return 0; },
        autolistBox: 'calculator_autolist',
        autolistInputField: 'calculator_choose_model',
        autolistElemClickHandler: addToCalculator
    });
    CalcAutolist.run();
}

function addToCalculator(e){
    var current_elem = CalcAutolist.getAutolistElemByTarget(e);
    var id = $(current_elem).attr('prin_id');
    var prin = $(current_elem).attr('prin');
    Calculator.addItem(id, prin);
    Calculator.showResultsButton();
}

function ItemHepler(){
    this.initItemData = function(id, prin, item){
        item.db_id = id;
        item.item_id = prin+'_'+id;
        item.prin = prin;
        item.request_cmd = prin;
        item.service_count = 1;
        item.enabled = false;
    };

    this.getItemDbData = function(item){
        $.ajax({
            url: '/request/calculator/'+item.request_cmd,
            type: 'POST',
            dataType: 'json',
            data: {id: item.db_id },
            success: function(jsonData){
                // console.log(jsonData);
                if(jsonData['error']){
                    console.log(jsonData['error']);
                } else {
                    item.initDbData(jsonData).addHtmlToSelectList();
                }
            },
            error: function(jqXHR, status, msg){
                console.log(jqXHR); console.log(msg+' '+status);
            }
        });
    };
}

function Cartridge(){
    //let obj = this;

    this.chipEnabled = false;
    this.chip_count = 1;

    this.getChipTotalPrice = function(){
        return this.chip_count * this.data['chip1'];
    };

    this.setChipCount = function(val){
        this.chip_count = val;
    };

    this.switchChipEnabled = function(){
        this.chipEnabled = this.chipEnabled ? false : true;
    };

    this.initDbData = function(db_data){
        this.data = db_data;
        return this;
    };

    this.addHtmlToSelectList = function(){
        let $main = CalcWriter.SelectedList.createMainContainer(this);

        let $cartridge_html = this.createCartridgeHtml(true);

        $main.append( $cartridge_html );
        CalcWriter.SelectedList.print($main);
    };

    this.createCartridgeHtml = function(enabled){
        let $cart_container = CalcWriter.SelectedList.cartridge.createContainer();
        let $main_info = CalcWriter.SelectedList.cartridge.createMainInfoBlock(this.db_id);
        $main_info.append( CalcWriter.SelectedList.cartridge.createName(this, enabled) );

        let services = this.getServises();
        if(services){
            $main_info.append( CalcWriter.SelectedList.cartridge.createServices(services, this) );
            $main_info.append( CalcWriter.SelectedList.cartridge.createCount(this) );
        }
        $cart_container.append($main_info);

        let $cartInfo = CalcWriter.SelectedList.cartridge.createInfoBlock(this, services);
        $cart_container.append($cartInfo);

        return $cart_container;
    };

    this.getServises = function(){
        let servises = [];
        if(this.data['zp1'] > 1){
            servises.push({name: 'заправка картриджа', id: 1});
        }
        if(this.data['onkj'] > 1){
            servises.push({name: 'оригинальный картридж', id: 4});
        }
        if(this.data['nonkj'] > 1){
            servises.push({name: 'неоригинальный картридж', id: 5});
        }
        if(servises.length > 0){
            this.setService(servises[0].id, servises[0].name);
            return servises;
        }
        return false;
    };

    this.setCount = function(val){
        this.service_count = parseInt(val);
    };

    this.setService = function(id, name){
        this.serviceId = parseInt(id);
        this.serviceName = name;
    };

    this.getPrice = function(){
        var price = 0;
        switch(this.serviceId){
            case 1:
                let zp_price_field;
                let total_zp_count = Calculator.zpCount;
                if(total_zp_count === 1){
                    zp_price_field = 'zp1';
                } else if (total_zp_count < 5) {
                    zp_price_field = 'zp2';
                } else if (total_zp_count < 10) {
                    zp_price_field = 'zp3';
                } else {
                    zp_price_field = 'zp4';
                }
                price = this.data[zp_price_field];
                break;
            case 4:
                price = this.data['onkj_rub'];
                break;
            case 5:
                price = this.data['nonkj_rub'];
                break;
        }
        return price;
    };

    this.getTotalPrice = function(){
        return this.getPrice() * this.service_count;
    };

    this.enableSwitch = function(elem){
        if(this.enabled){
            this.enabled = false;
            $(elem).removeClass('cart_of_app_checked').addClass('cart_of_app');
        } else {
            this.enabled = true;
            $(elem).removeClass('cart_of_app').addClass('cart_of_app_checked');
        }
    };

    this.getResultsRow = function(){
        if(!this.enabled){
            return;
        }
        let name = this.serviceName + ' ' + this.data['brand_name'] + ' ' + this.data['name'];
        let row = CalcWriter.resultsTable.createRow(
            name,
            this.service_count,
            this.getPrice(),
            this.getTotalPrice(),
            this
        );
        if(this.data['chip1'] > 1 && this.serviceId === 1){
            let $chip_row = CalcWriter.resultsTable.createCartridgeChip( this );
            row = $(row).add( $chip_row );
        }
        return row;
    };

    this.getZpCount = function(){
        return (this.serviceId === 1 && this.enabled) ? this.service_count : 0;
    };

    this.getItemSum = function(){
        if(!this.enabled){
            return 0;
        }
        let s = 0;

        if(this.data['chip_unessential'] == 0 && this.serviceId === 1 && this.data['chip1'] > 1){
            this.chipEnabled = true;
            this.chip_count = this.chip_count < this.service_count ? this.service_count : this.chip_count;
        }

        if(this.chipEnabled){
            s += this.getChipTotalPrice();
        }
        s += this.getTotalPrice();
        return s;
    };

    this.getOperatorStr = function(){
        let result = [];
        if(!this.enabled){
            return false;
        }

        let modelname = this.data['color'] > 0 ? this.data['name'] : this.data['cartridge'];

        // если это заправка и нужны чипы и количество совпадает
        // записываем общую строку и заканичиваем работу.
        if(this.serviceId === 1 && this.chipEnabled && this.chip_count == this.service_count){
            let zp_chip_price = parseInt(this.getPrice()) + parseInt( this.data['chip1'] );
            let count = (this.service_count > 1) ? '*' + this.service_count : '';
            result.push( modelname + ' зп + чип ' + zp_chip_price + count );
            return result;
        }

        // запишем услугу:
        if(this.service_count > 0){
            let serv;
            switch(this.serviceId){
                case 1: serv = 'зп'; break;
                case 4: serv = 'нкж'; break;
                case 5: serv = 'неор кж'; break;
            }
            let price = this.getPrice();
            let count = (this.service_count > 1) ? '*' + this.service_count : '';
            result.push( modelname + ' ' + serv + ' ' + price + count );
        }

        // и допишем чип
        if(this.chipEnabled && this.chip_count > 0){
            let count = (this.chip_count > 1) ? '*' + this.chip_count : '';
            let chip_str = (result.length > 0) ? ' чип ' : modelname + ' чип ';
            result.push( chip_str + this.data['chip1'] + count );
        }

        return result;
    };

}

function Apparat(){
    //let obj = this;

    this.firmwareEnabled = false;
    this.firmware_count = 1;

    this.initDbData = function(db_data){
        this.data = db_data['apparat'];
        this.data['cartridges'] = [];
        for(var i in db_data['cartridges']){
            var c = new Cartridge();
            ItemHepler.initItemData(db_data['cartridges'][i]['id'], 2, c);
            c.data = db_data['cartridges'][i];
            this.data['cartridges'].push(c);
        }
        return this;
    };

    this.getFirmwareTotalPrice = function(){
        return this.firmware_count * this.data['firmware'];
    };

    this.setFirmwareCount = function(val){
        this.firmware_count = val;
    };

    this.addHtmlToSelectList = function(){
        let $main = CalcWriter.SelectedList.createMainContainer(this);
        $main.append(CalcWriter.SelectedList.createAppHeader(this));
        for(var k in this.data.cartridges){
            let $cartridge_html = this.data.cartridges[k].createCartridgeHtml(false);
            $main.append( $cartridge_html );
        }
        CalcWriter.SelectedList.print($main);
    };

    this.getResultsRow = function(){
        var rows = [];
        rows.push( CalcWriter.resultsTable.createApparatHeader(this) );
        if(this.data['firmware'] > 1){
            rows.push( CalcWriter.resultsTable.createApparatProshivka(this) );
        }

        var isCartridges = false;
        for(var k in this.data.cartridges){
            let cartridge_row = this.data.cartridges[k].getResultsRow();
            if(cartridge_row){
                rows.push( cartridge_row );
                isCartridges = true;
            }
        }

        return isCartridges ? rows : '';
    };

    this.switchFirmwareEnabled = function(){
        this.firmwareEnabled = this.firmwareEnabled ? false : true;
    };

    this.getZpCount = function(){
        let cnt = 0;
        for(var k in this.data.cartridges){
            cnt += this.data.cartridges[k].getZpCount();
        }
        return cnt;
    };

    this.getItemSum = function(){
        let s = 0;
        if(this.firmwareEnabled){
            s += this.getFirmwareTotalPrice();
        }
        for(var k in this.data.cartridges){
            s += this.data.cartridges[k].getItemSum();
        }
        return s;
    };

    this.getOperatorStr = function(){
        let result = [];
        if(this.firmwareEnabled && this.firmware_count > 0){
            let count = (this.firmware_count > 1) ? '*' + this.firmware_count : '';
            result.push( this.data['app_name'] + ' прошивка ' + this.data['firmware'] + count );
        }
        for(var k in this.data.cartridges){
            let cart_arr = this.data.cartridges[k].getOperatorStr();
            for(var i in cart_arr){
                result.push( cart_arr[i] );
            }
        }
        return result;
    };

}

function Calculator(){
    this.items = {};

    this.zpCount = 0;
    this.totalSum = 0;

    this.recount = function(){
        this.zpCount = 0;
        for(var k in this.items){
            this.zpCount += this.items[k].getZpCount();
        }
        this.recountSum();
    };

    this.recountSum = function(){
        this.totalSum = 0;
        for(var k in this.items){
            this.totalSum += this.items[k].getItemSum();
        }
    };

    this.addItem = function(id, prin){
        if(prin == 2){
            var item = new Cartridge();
        } else if (prin == 1) {
            var item = new Apparat();
        }
        ItemHepler.initItemData(id, prin, item);
        ItemHepler.getItemDbData(item);
        this.items[Object.keys(Calculator.items).length] = item;
        return item;
    };

    this.showResultsButton = function(){
        var $btn = $('#btn_getCalculationResults');
        if(Object.keys(this.items).length > 0){
            $btn.css('display', 'inline-block');
        } else {
            $btn.css('display', 'none');
        }
    };

    this.showCalculationResults = function(){
        Calculator.recount();
        CalcWriter.resultsTable.clear();
        CalcWriter.addContentToResultsTable(this.items);
        CalcWriter.resultsTable.printTotalSumm();
        CalcWriter.resultsTable.printOperatorString();
        CalcWriter.showResultsContainer();
    };

    this.reset = function(){
        $('#calc_step_three').hide();
        $('#btn_getCalculationResults').hide();
        CalcWriter.resultsTable.clear();
        CalcWriter.SelectedList.clear();
        Calculator.items = {};
        $('html, body').animate({
            scrollTop: $('#calc_step_two').offset().top - 40
        }, 300);
    };

}

function CalcWriter(){

    this.showResultsContainer = function(){
        $('#calc_step_three').show();
    };

    this.scrollToResultsContainer = function(){
        $('html, body').animate({
            scrollTop: $('#calc_step_three').offset().top - 40
        }, 300);
    };

    this.addContentToResultsTable = function(items){
        var $table = $('#CalculationResults');
        $table.append( this.resultsTable.createHeader() );
        for(var i in items){
            let $row = items[i].getResultsRow();
            if($row){
                $table.append( $row );
            }
            $table.append( CalcWriter.resultsTable.rowsSeparator() );
        }
        $table.append( CalcWriter.resultsTable.getDeliveryRow() );
    };

    this.resultsTable = {

        clear: function(){
            $('#CalculationResults').html('');
            $('#oprstr').html('');
        },

        printTotalSumm: function(){
            let $row = $('<tr>');
            $('<td>', {colspan: 3, class: 'results_total_sum_text', text: 'Итого: '}).appendTo($row);
            $('<td>', {class: 'results_total_sum', text: Calculator.totalSum + 'р.'}).appendTo($row);
            $('#CalculationResults').append($row);
        },

        printOperatorString: function(){
            let items = Calculator.items;
            let total_strs = [];
            for(var i in items){
                var strs = items[i].getOperatorStr();
                for(var k in strs){
                    total_strs.push( strs[k] );
                }
            }
            $('#oprstr').html(total_strs.join(', '));
        },

        createHeader: function(){
            let $row = $('<tr>', {class: 'resTableHeader'});
            $('<td>', {text: 'Наименование услуги'}).appendTo($row);
            $('<td>', {class: 'tdcenter', text: 'Кол-во'}).appendTo($row);
            $('<td>', {class: 'tdcenter', text: 'Цена'}).appendTo($row);
            $('<td>', {class: 'tdcenter', text: 'Стоимость'}).appendTo($row);
            return $row;
        },
        createRow: function(name, count, price, total_price, cart_obj){
            let $row = $('<tr>');
            $('<td>', {text: name}).appendTo($row);

            var $td_count = $('<td>', {class: 'tdcenter'});
            var $service_count = $('<input>', {value: count});
            $service_count.on('change', function(){
                cart_obj.setCount($(this).val());
                Calculator.showCalculationResults();
            });
            $td_count.append($service_count);
            $row.append($td_count);

            $('<td>', {class: 'tdcenter', text: price}).appendTo($row);
            $('<td>', {class: 'tdcenter service_total', text: total_price}).appendTo($row);
            return $row;
        },
        createApparatHeader: function(app_obj){
            let $row = $('<tr>');
            $('<td>', {
                text: app_obj.data['brand_name'] + ' ' + app_obj.data['app_name'],
                colspan: 4,
                css: {'padding': '6px 9px', 'font-size': '12px', 'font-weight' : 'bold'}
            }).appendTo($row);
            return $row;
        },
        createCartridgeChip: function(cart_obj){
            let $row = $('<tr>');
            let $td = $('<td>', {class: 'additional_service'});
            let $checkbox = $('<input>', {
                type: 'checkbox',
                value: 1,
                click: function(){
                    cart_obj.switchChipEnabled();
                    if(cart_obj.chipEnabled){ cart_obj.chip_count = cart_obj.service_count; }
                    Calculator.showCalculationResults();
                    /*
                    CalcWriter.resultsTable.toggleHideableFields(this);
                    Calculator.recount();
                    $('.results_total_sum').html( Calculator.totalSum + 'р.' );
                    CalcWriter.resultsTable.printOperatorString();
                    */
                },
                css: {'margin': '0 5px 0 0'}
            });
            if(cart_obj.chipEnabled){
                $checkbox.attr('checked', 'checked');
            }
            if(cart_obj.data['chip_unessential'] == 0){
                $checkbox.attr('disabled', 'disabled');
            }
            $checkbox.appendTo($td);

            $td.append(document.createTextNode('Добавить замену чипа ('+cart_obj.data['chip1']+'р.)'));
            let $helpBtn = $('<span>', {class: 'popup_button', text: '?'});
            addPopup($helpBtn, cart_obj.data['chip_status_text']);
            $td.append($helpBtn);
            $row.append($td);
            var hidden_class_str = cart_obj.chipEnabled ? '' : 'calc_hidden';

            var $td_count = $('<td>', {class: 'additional_service tdcenter'});
            var $service_count = $('<input>', {value: cart_obj.chip_count});
            $service_count.on('change', function(){
                cart_obj.setChipCount( $(this).val() );
                Calculator.showCalculationResults();
            });
            if(!cart_obj.chipEnabled){
                $service_count.hide();
            }
            $td_count.append($service_count);
            $row.append($td_count);

            $('<td>', {class: 'additional_service tdcenter hideable '+hidden_class_str, text: cart_obj.data['chip1']}).appendTo($row);

            $('<td>', {class: 'additional_service tdcenter hideable '+hidden_class_str, text: cart_obj.getChipTotalPrice()}).appendTo($row);

            return $row;
        },
        createApparatProshivka: function(app_obj){
            let $row = $('<tr>');
            let $td = $('<td>', {class: 'additional_service'});
            let $checkbox = $('<input>', {
                type: 'checkbox',
                value: 1,
                click: function(){
                    app_obj.switchFirmwareEnabled();
                    Calculator.showCalculationResults();
                    /*
                    CalcWriter.resultsTable.toggleHideableFields(this);
                    Calculator.recount();
                    $('.results_total_sum').html( Calculator.totalSum + 'р.' );
                    CalcWriter.resultsTable.printOperatorString();
                    */
                },
                css: {'margin': '0 5px 0 0'}
            });
            if(app_obj.firmwareEnabled){
                $checkbox.attr('checked', 'checked');
            }
            $checkbox.appendTo($td);

            $td.append(document.createTextNode('Добавить прошивку ('+app_obj.data['firmware']+'р.)'));
            let $helpBtn = $('<span>', {class: 'popup_button', text: '?'});
            addPopup($helpBtn, '<strong>Прошивка</strong> — перепрограммирование устройства для возможности заправлять картриджи без замены чипа. Делается один раз и навсегда, тогда как чипы необходимо менять при каждой заправке.');
            $td.append($helpBtn);
            $row.append($td);
            var hidden_class_str = app_obj.firmwareEnabled ? '' : 'calc_hidden';

            var $td_count = $('<td>', {class: 'additional_service tdcenter'});
            var $service_count = $('<input>', {value: app_obj.firmware_count});
            $service_count.on('change', function(){
                app_obj.setFirmwareCount( $(this).val() );
                Calculator.showCalculationResults();
            });
            if(!app_obj.firmwareEnabled){
                $service_count.hide();
            }
            $td_count.append($service_count);
            $row.append($td_count);

            $('<td>', {class: 'additional_service tdcenter hideable '+hidden_class_str, text: app_obj.data['firmware']}).appendTo($row);

            $('<td>', {class: 'additional_service tdcenter hideable '+hidden_class_str, text: app_obj.getFirmwareTotalPrice()}).appendTo($row);

            return $row;
        },
        rowsSeparator: function(){
            let $row = $('<tr>');
            $('<td>', {
                colspan: 4,
                css: {
                    'height': '24px',
                    'background-color': 'transparent',
                    'border': 'none',
                    'padding': '0'
                }
            }).appendTo($row);
            return $row;
        },
        getDeliveryRow: function(){
            let $row = $('<tr>');
            $('<td>', {colspan: 3, html: 'Выезд / доставка — <b>БЕСПЛАТНО</b>'}).appendTo($row);
            $('<td>', {text: 0, class: 'tdcenter'}).appendTo($row);
            return $row;
        },
        toggleHideableFields: function(elem){
            let elems = $(elem).parent().parent().find('.hideable');
            if($(elems[0]).hasClass('calc_hidden')){
                $(elems).removeClass('calc_hidden');
                $(elem).parent().next().find('input').show();
            } else {
                $(elems).addClass('calc_hidden');
                $(elem).parent().next().find('input').hide();
            }
        }
    };

    this.SelectedList = {

        clear: function(){
            $('#selected_models').html('');
        },

        createMainContainer: function(item_obj){
            return $('<div>', {item_id: item_obj.db_id, item_prin: item_obj.prin});
        },

        createAppHeader: function(item_obj){
            var $app_container = $('<div>', {class: 'apparat_container'});
            var $app_name = $('<div>', {
                class: 'apparat_name',
                html: 'Для '+item_obj.data['deviceType']['wordform3']+' <b>'+item_obj.data['brand_name']+' '+item_obj.data['app_name']+'</b> подходят картриджи:'});
            $app_container.append($app_name);
            return $app_container;
        },

        cartridge: {
            createContainer: function(){
                return $('<div>');
            },

            createMainInfoBlock: function(cartridge_id){
                return $('<div>', {class: 'model_main_info', cartridge_id: cartridge_id});
            },

            createName: function(cart_obj, enabled){
                let $name = $('<span>', {text: cart_obj['data']['cartridge'], class: 'cart_of_app'});
                $name.on('click', function(){
                    cart_obj.enableSwitch($name);
                });
                if(enabled){
                    cart_obj.enableSwitch($name);
                }
                return $name;
            },

            createServices: function(services, cart_obj){
                var $service_select = $('<select>', {class: 'service_name'});
                // if any services available
                for(let k = 0; k < services.length; k++){
                    let $option = $('<option>', {text: services[k].name, value: services[k].id});
                    $service_select.append($option);
                }
                $service_select.on('change', function(){
                    cart_obj.setService($(this).val(), $(this).find('option:selected').text());
                });
                return $service_select;
            },

            createCount: function(cart_obj){
                var $service_count = $('<input>', {value: 1});
                $service_count.on('change', function(){
                    cart_obj.setCount($(this).val());
                });
                return $service_count;
            },

            createInfoBlock: function(cart_obj, services){
                var colornames = ['черный', 'черный', 'голубой', 'желтый', 'пурпурный'];
                var $cart_info = $('<div>', {class: 'model_controls'});
                $('<div>', {text: 'Ресурс: '+cart_obj['data']['resurs']}).appendTo($cart_info);
                $('<div>', {class: 'cartrcolor'+cart_obj['data']['color'], text: colornames[cart_obj['data']['color']]}).appendTo($cart_info);
                var msg = false;
                if(services.length == 0){
                    msg = 'В данный момент нет расходных материалов.';
                } else if(cart_obj['data']['drum'] > 0){
                    msg = '<span style="color: red;">Драм не заправляется</span>';
                } else if (!cart_obj['data']['zp1'] > 1) {
                    msg = '<span style="color: red;">Картридж не заправляется</span>';
                }
                if(msg){
                    $('<div>', {html: msg}).appendTo($cart_info);
                }
                return $cart_info;
            }
        },

        print: function(elem){
            $('#selected_models').append(elem);
        }
    };

    this.orderContent = {

        id: 123,

        /**
         * @param {Apparat|Cartridge} items
         */
        createContentHtml: function(items){
            let $order_content_container = $('#order_content_contaner');
            for(var i in items){
                var item_obj = items[i];
                if(item_obj.prin == 1){
                    // аппарат
                    if(item_obj.firmwareEnabled && item_obj.firmware_count > 0){
                        $order_content_container.append( CalcWriter.orderContent.getFirmware(item_obj) );
                    }

                    for(var k in item_obj.data['cartridges']){
                        $order_content_container.append( CalcWriter.orderContent.getCartridgeData(item_obj.data['cartridges'][k]), item_obj );
                    }

                } else {
                    $order_content_container.append( CalcWriter.orderContent.getCartridgeData(item_obj, false) );
                }
            }
        },

        /**
         * @param {Cartridge} cart_obj
         * @param {Apparat} app_obj
         */
        getCartridgeData: function(cart_obj, app_obj){
            let $res = $('<div>');
            if(!cart_obj.enabled){
                return $res;
            }

            if(cart_obj.chipEnabled && cart_obj.chip_count > 0){
                $res.append( CalcWriter.orderContent.getChip(cart_obj, app_obj) );
            }
            switch(cart_obj.serviceId){
                case 1: $res.append( CalcWriter.orderContent.getRefill(cart_obj, app_obj) ); break;
                case 4: $res.append( CalcWriter.orderContent.getOnkj(cart_obj, app_obj) ); break;
                case 5: $res.append( CalcWriter.orderContent.getNonkj(cart_obj, app_obj) ); break;
            }
            return $res;
        },

        getOnkj: function(cart_obj, app_obj){
            let $span = $('<span>', {
                'order_content': CalcWriter.orderContent.id,
                'data-cartridge_id': cart_obj.db_id,
                'data-usluga': 4,
                'data-usluga_count': cart_obj.service_count,
                'data-onkj': cart_obj.data['onkj'],
                'data-brand_id': cart_obj.data['brand_id'],
                'data-cartridge_name': cart_obj.data['brand_name']+' '+cart_obj.data['name']
            });
            if(app_obj){
                $span.attr('data-app_id', app_obj.db_id);
                $span.attr('data-app_name', app_obj.data['app_name']);
            }
            return $span;
        },

        getNonkj: function(cart_obj, app_obj){
            let $span = $('<span>', {
                'order_content': CalcWriter.orderContent.id,
                'data-cartridge_id': cart_obj.db_id,
                'data-usluga': 5,
                'data-usluga_count': cart_obj.service_count,
                'data-nonkj': cart_obj.data['nonkj'],
                'data-brand_id': cart_obj.data['brand_id'],
                'data-cartridge_name': cart_obj.data['brand_name']+' '+cart_obj.data['name']
            });
            if(app_obj){
                $span.attr('data-app_id', app_obj.db_id);
                $span.attr('data-app_name', app_obj.data['app_name']);
            }
            return $span;
        },

        /**
         * @param {Cartridge} cart_obj
         * @param {Apparat} app_obj
         */
        getRefill: function(cart_obj, app_obj){
            let $span = $('<span>', {
                'order_content': CalcWriter.orderContent.id,
                'data-cartridge_id': cart_obj.db_id,
                'data-usluga': 1,
                'data-usluga_count': cart_obj.service_count,
                'data-zp1': cart_obj.data['zp1'],
                'data-zp2': cart_obj.data['zp2'],
                'data-zp3': cart_obj.data['zp3'],
                'data-zp4': cart_obj.data['zp4'],
                'data-brand_id': cart_obj.data['brand_id'],
                'data-cartridge_name': cart_obj.data['brand_name']+' '+cart_obj.data['name']
            });
            if(app_obj){
                $span.attr('data-app_id', app_obj.db_id);
                $span.attr('data-app_name', app_obj.data['app_name']);
            }
            return $span;
        },

        /**
         * @param {Cartridge} cart_obj
         */
        getChip: function(cart_obj, app_obj){
            let $span = $('<span>', {
                'order_content': CalcWriter.orderContent.id,
                'data-cartridge_id': cart_obj.db_id,
                'data-usluga': 2,
                'data-usluga_count': cart_obj.chip_count,
                'data-chip1': cart_obj.data['chip1'],
                'data-brand_id': cart_obj.data['brand_id'],
                'data-cartridge_name': cart_obj.data['brand_name']+' '+cart_obj.data['name']
            });
            if(app_obj){
                $span.attr('data-app_id', app_obj.db_id);
                $span.attr('data-app_name', app_obj.data['app_name']);
            }
            return $span;
        },

        /**
         * @param {Apparat} app_obj
         */
        getFirmware: function(app_obj){
            let $span = $('<span>', {
                'order_content': CalcWriter.orderContent.id,
                'data-app_id': app_obj.db_id,
                'data-usluga': 3,
                'data-usluga_count': app_obj.firmware_count,
                'data-proshivka': app_obj.data['firmware'],
                'data-brand_id': app_obj.data['brand_id'],
                'data-app_name': app_obj.data['brand_name']+' '+app_obj.data['app_name']
            });
            return $span;
        }

    };


}