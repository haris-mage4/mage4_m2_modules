require(['jquery'], function($) {
    $(document).ready(function() {

        function getNextFriday(date = new Date()) {
            const dateCopy = new Date(date.getTime());

            const nextFriday = new Date(
                dateCopy.setDate(
                    dateCopy.getDate() + ((7 - dateCopy.getDay() + 5) % 7 || 7),
                ),
            );
            return nextFriday;
        }

        function addWeeks(date, weeks) {
            date.setDate(date.getDate() + 7 * weeks);
            return date;
        }

        window.numPayments = 0;
        window.paymentAmount = 0;
        window.paymentLast = 0;
        window.creditLimit = 0;
        window.risaURL = null;
        window.setRisaURL = null;
        window.setRisaSet = 0;

        window.isSet = false;
        window.initialTotal = 0;
        window.doingTheClick = 0;

        var checkExist = setInterval(function() {
            if ($('#checkout-payment-method-load > div').length) {
                clearInterval(checkExist);

                if(jQuery("#payment > div:nth-child(1)").text() != "Payment Method") {
                    var tmp = jQuery("#checkout-payment-method-load > div > div > div.step-title");
                    tmp.remove();
                    jQuery("#payment").prepend(tmp);
                    jQuery("#checkout-step-payment").css({'margin-top':'-16px !important;'});
                    jQuery("#co-payment-form > fieldset > br").remove();
                }

                jQuery("#checkout-payment-method-load > div > div > div.step-title").hide();
            }
        }, 500);

        function setSessionTerms(theTerm) {
            $.ajax({
                url: "/setTerms.php?term=" + theTerm,
                method: "GET",
                success: function(response) {
                    // handle the success response
                    console.log("Data retrieved successfully");
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    // handle the error response
                    console.log("Error retrieving data: " + error);
                }
            });
        }

        var checkExist = setInterval(function() {
            if ($('div.customerbalance').length) {
                clearInterval(checkExist);

                var check2Exist = setInterval(function() {
                    if ($('#opc-sidebar > div.opc-block-summary > table > tbody > tr.grand.totals > td > strong > span').length) {
                        clearInterval(check2Exist);

                        jQuery("#customerbalance-placer > div.payment-option-content.field.choice > div.payment-option-inner > span:nth-child(2)").prependTo(jQuery("#customerbalance-placer > div.payment-option-content.field.choice > div.payment-option-inner > span:nth-child(2)").html("Available Balance: ").parent());

                        var check3Exist = setInterval(function() {
                            if ($('div.checkout-agreement > label').length) {
                                clearInterval(check3Exist);

                                jQuery("div.customerbalance").each(function() {
                                    jQuery(this).parent().prepend(this);
                                });
                                jQuery("div.discount-code").remove();
                                jQuery("div.giftcardaccount").remove();
                                var theDIV = $('#customerbalance-placer > div.payment-option-content.field.choice > div.payment-option-inner > span:nth-child(2)');
                                var text = theDIV.text().replace("Store ", "");
                                theDIV.text(text);

                                window.creditLimit = jQuery("#customerbalance-available-amount").text().replace("$", "").replace(",", "");

                                var orderTotal = jQuery("#opc-sidebar > div.opc-block-summary > table > tbody > tr.grand.totals > td > strong > span").text().replace("$", "").replace(",", "");

                                if(parseInt(orderTotal) < parseInt(window.creditLimit)) {
                                    window.creditLimit = orderTotal;
                                }

                                var limit3 = Math.ceil((window.creditLimit / 6) * 100) / 100;
                                var limit3last = window.creditLimit - (limit3 * 5);
                                var limit3total = (limit3 * 5) + limit3last;


                                var limit6 = Math.ceil((window.creditLimit / 12) * 100) / 100;
                                var limit6last = window.creditLimit - (limit6 * 1);
                                var limit6total = (limit6 * 11) + limit6last;

                                var limit9 = Math.ceil((window.creditLimit / 18) * 100) / 100;
                                var limit9last = window.creditLimit - (limit9 * 17);
                                var limit9total = (limit9 * 17) + limit9last;

                                var limit12 = Math.ceil((window.creditLimit / 26) * 100) / 100;
                                var limit12last = window.creditLimit - (limit12 * 25);
                                var limit12total = (limit12 * 25) + limit12last;

                                window.numPayments = 6;
                                window.paymentAmount = limit3;
                                window.paymentLast = limit3last;


                                setSessionTerms("3months");


                                jQuery("#customerbalance-placer > div.payment-option-content.field.choice > div.actions-toolbar").prepend(`<table id="termstable" class="table-checkout-partial-payment">
                                    <thead>
                                    <tr class="row">
                                        <th class="col col-method">Select Term</th>
                                        <th class="col col-emi-amount">Payment Amount</th>
                                        <th class="col col-total-paid">Total Paid</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <tr class="row">
                                        <td class="col col-method">
                                            <input type="radio" class="radio" value="3-months" name="payment_terms" id="3monthterm" checked> 3 months
                                        </td>
                                        <td class="col col-emi-amount">
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">` + limit3.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 5 Payments</span><br />
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">` + limit3last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 1 Final Payment</span>
                                        </td>
                                        <td class="col col-total-paid">
                                            <span class="price"><span class="price" data-bind="text: element.getTotalAmount(option)">$` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                        </td>
                                    </tr>
                                    <tr class="row">
                                        <td class="col col-method">
                                            <input type="radio" class="radio" value="6-months" name="payment_terms" id="6monthterm" > 6 months
                                        </td>
                                        <td class="col col-emi-amount">
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">$` + limit6 + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 11 Payments</span><br />
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">` + limit6last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 1 Final Payment</span>
                                        </td>
                                        <td class="col col-total-paid">
                                            <span class="price"><span class="price" data-bind="text: element.getTotalAmount(option)">$` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                        </td>
                                    </tr>
                                    <tr class="row">
                                        <td class="col col-method">
                                            <input type="radio" class="radio" value="9-months" name="payment_terms" id="9monthterm" > 9 months
                                        </td>
                                        <td class="col col-emi-amount">
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">$` + limit9 + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 17 Payments</span><br />
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">` + limit9last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 1 Final Payment</span>
                                        </td>
                                        <td class="col col-total-paid">
                                            <span class="price"><span class="price" data-bind="text: element.getTotalAmount(option)">$` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                        </td>
                                    </tr>
                                    <tr class="row">
                                        <td class="col col-method">
                                            <input type="radio" class="radio" value="9-months" name="payment_terms" id="12monthterm" > 12 months
                                        </td>
                                        <td class="col col-emi-amount">
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">$` + limit12 + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 25 Payments</span><br />
                                            <span class="price"><span class="price" data-bind="text: element.getInstallmentAmount(option)">` + limit12last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                            <span class="period" data-bind="i18n: ' X '+element.getInstallment(option)"> X 1 Final Payment</span>
                                        </td>
                                        <td class="col col-total-paid">
                                            <span class="price"><span class="price" data-bind="text: element.getTotalAmount(option)">$` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `</span></span>
                                        </td>
                                    </tr>
                                    </tbody>
                                    </table><br />`);

                                var currentDate = new Date();
                                var daysToAdd = 5 - currentDate.getDay(); // days until next Friday
                                if (daysToAdd < 0) {
                                  daysToAdd += 7; // add 7 days if the current day is after Friday
                                }
                                var nextFriday = getNextFriday();
                                var nextFriday12Weeks = getNextFriday();
                                var nextFriday24Weeks = getNextFriday();
                                var nextFriday36Weeks = getNextFriday();
                                var nextFriday52Weeks = getNextFriday();

                                var nextFriday12Weeks = addWeeks(nextFriday12Weeks, 12);
                                var nextFriday24Weeks = addWeeks(nextFriday24Weeks, 24);
                                var nextFriday36Weeks = addWeeks(nextFriday36Weeks, 36);
                                var nextFriday52Weeks = addWeeks(nextFriday52Weeks, 52);


                                var formattedDate = (nextFriday.getMonth() + 1) + '/' + nextFriday.getDate() + '/' + nextFriday.getFullYear();
                                var formattedDate12Weeks = (nextFriday12Weeks.getMonth() + 1) + "/" + nextFriday12Weeks.getDate() + "/" + nextFriday12Weeks.getFullYear();
                                var formattedDate24Weeks = (nextFriday24Weeks.getMonth() + 1) + "/" + nextFriday24Weeks.getDate() + "/" + nextFriday24Weeks.getFullYear();
                                var formattedDate36Weeks = (nextFriday36Weeks.getMonth() + 1) + "/" + nextFriday36Weeks.getDate() + "/" + nextFriday36Weeks.getFullYear();
                                var formattedDate52Weeks = (nextFriday52Weeks.getMonth() + 1) + "/" + nextFriday52Weeks.getDate() + "/" + nextFriday52Weeks.getFullYear();


                                jQuery("#termstable").parent().append(`
                                <div id='breakdown3'>
        Your Payment Plan is shown below<br /><br />

        Total Of Payments $` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br />
        APR 0%<br />
        Interest $0.00
        <br /><br />
        12 Total Payments
        <br /><br />

        Beginning on ` + formattedDate + ` $` + limit3 + ` + 5 more payments<br />
        Final Payment on ` + formattedDate12Weeks + ` of ` + limit3last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br /><br />

        *these dates are close estimates.</div>`);

                                jQuery("#termstable").parent().append(`
                                <div id='breakdown6' style='display: none;'>
        Your Payment Plan is shown below<br /><br />

        Total Of Payments $` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br />
        APR 0%<br />
        Interest $0.00
        <br /><br />
        24 Total Payments
        <br /><br />

        Beginning on ` + formattedDate + ` $` + limit6 + ` + 15 more payments<br />
        Final Payment on ` + formattedDate24Weeks + ` of ` + limit6last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br /><br />

        *these dates are close estimates.</div>`);

                                jQuery("#termstable").parent().append(`
                                <div id='breakdown9' style='display: none;'>
        Your Payment Plan is shown below<br /><br />

        Total Of Payments $` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br />
        APR 0%<br />
        Interest $0.00
        <br /><br />
        36 Total Payments
        <br /><br />

        Beginning on ` + formattedDate + ` $` + limit9 + ` + 17 more payments<br />
        Final Payment on ` + formattedDate36Weeks + ` of ` + limit9last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br /><br />

        *these dates are close estimates.</div>`);

                                jQuery("#termstable").parent().append(`
                                <div id='breakdown12' style='display: none;'>
        Your Payment Plan is shown below<br /><br />

        Total Of Payments $` + window.creditLimit.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br />
        APR 0%<br />
        Interest $0.00
        <br /><br />
        52 Total Payments
        <br /><br />

        Beginning on ` + formattedDate + ` $` + limit12 + ` + 25 more payments<br />
        Final Payment on ` + formattedDate52Weeks + ` of ` + limit12last.toLocaleString('en-US', {style: 'currency', currency: 'USD'}) + `<br /><br />

        *these dates are close estimates.</div>`);


                                jQuery("#3monthterm").click(function() {
                                    setSessionTerms("3months");
                                    jQuery("#breakdown3").show();
                                    jQuery("#breakdown6").hide();
                                    jQuery("#breakdown9").hide();
                                    jQuery("#breakdown12").hide();
                                    window.numPayments = 12;
                                    window.paymentAmount = limit3;
                                    window.paymentLast = limit3last;
                                    setTerms
                                });

                                jQuery("#6monthterm").click(function() {
                                    setSessionTerms("6months");
                                    jQuery("#breakdown3").hide();
                                    jQuery("#breakdown6").show();
                                    jQuery("#breakdown9").hide();
                                    jQuery("#breakdown12").hide();
                                    window.numPayments = 24;
                                    window.paymentAmount = limit6;
                                    window.paymentLast = limit6last;
                                });

                                jQuery("#9monthterm").click(function() {
                                    setSessionTerms("9months");
                                    jQuery("#breakdown3").hide();
                                    jQuery("#breakdown6").hide();
                                    jQuery("#breakdown9").show();
                                    jQuery("#breakdown12").hide();
                                    window.numPayments = 36;
                                    window.paymentAmount = limit9;
                                    window.paymentLast = limit9last;
                                });

                                jQuery("#12monthterm").click(function() {
                                    setSessionTerms("12months");
                                    jQuery("#breakdown3").hide();
                                    jQuery("#breakdown6").hide();
                                    jQuery("#breakdown9").hide();
                                    jQuery("#breakdown12").show();
                                    window.numPayments = 52;
                                    window.paymentAmount = limit12;
                                    window.paymentLast = limit12last;
                                });
                            }
                        }, 100);
                    }
                }, 100);
            }
        }, 100);

        setInterval(function() {
            if ($("body:contains('No Payment Information Required')").length > 0) {
                $(".checkout-billing-address").hide();
            } else {
                $(".checkout-billing-address").show();
            }
        }, 1000);

        setInterval(function() {
            if ($("body:contains('No Payment Information Required')").length > 0) {
                $(".checkout-billing-address").hide();
            } else {
                $(".checkout-billing-address").show();
            }
        }, 1000);




        setInterval(function() {

            if ((window.isSet == false) && ($('#opc-sidebar > div.opc-block-summary > table > tbody > tr.totals.balance > td > span').text() != '')) {

                window.initialTotal = parseFloat(jQuery("#opc-sidebar > div.opc-block-summary > table > tbody > tr.grand.totals > td > strong > span").text().replace("$", "").replace(",", ""));

                var newTotal = window.initialTotal + parseFloat(jQuery('#opc-sidebar > div.opc-block-summary > table > tbody > tr.totals.balance > td > span').text().replace("$", "").replace("-" ,"").replace(",", ""));

                jQuery("#opc-sidebar > div.opc-block-summary > table > tbody > tr.grand.totals > td > strong > span").html(newTotal.toLocaleString('en-US', {style: 'currency', currency: 'USD'}));
                jQuery("#opc-sidebar > div.opc-block-summary > table > tbody > tr.totals.balance > td").hide();

                window.isSet = true;
            } else {
                if (($('#opc-sidebar > div.opc-block-summary > table > tbody > tr.totals.balance > td > span').text() != '')) {

                    var newTotal = window.initialTotal + parseFloat(jQuery('#opc-sidebar > div.opc-block-summary > table > tbody > tr.totals.balance > td > span').text().replace("$", "").replace("-" ,"").replace(",", ""));

                    jQuery("#opc-sidebar > div.opc-block-summary > table > tbody > tr.grand.totals > td > strong > span").html(newTotal.toLocaleString('en-US', {style: 'currency', currency: 'USD'}));

                    jQuery("#opc-sidebar > div.opc-block-summary > table > tbody > tr.totals.balance > td").hide();
                }
            }

        }, 1000);




        var shippingExist = setInterval(function() {
            if ($('#checkout-shipping-method-load').length) {
                clearInterval(shippingExist);

                var tmp = jQuery("#checkout-shipping-method-load > table > thead > tr > th:nth-child(4)");
                tmp.remove();
                jQuery("#checkout-shipping-method-load > table > thead > tr > th:nth-child(1)").after(tmp);

                var tmp = jQuery("#checkout-shipping-method-load > table > thead > tr > th:nth-child(4)");
                tmp.remove();
                jQuery("#checkout-shipping-method-load > table > thead > tr > th:nth-child(2)").after(tmp);

                var tmp = jQuery("#checkout-shipping-method-load > table > tbody > tr > td:nth-child(4)");
                tmp.remove();
                jQuery("#checkout-shipping-method-load > table > tbody > tr > td:nth-child(1)").after(tmp);

                var tmp = jQuery("#checkout-shipping-method-load > table > tbody > tr > td:nth-child(4)");
                tmp.remove();
                jQuery("#checkout-shipping-method-load > table > tbody > tr > td:nth-child(2)").after(tmp);

                jQuery(".table-checkout-shipping-method tbody td:first-child").css("text-align", "center");

            }
        }, 100);

        function doAgreeClick() {

            var myArray = jQuery("div.shipping-address-items").find("div.selected-item").text().split("\n").map(function(str) {
              return str.trim();
            });

            if(window.creditLimit == 0) {
                window.creditLimit = jQuery("#opc-sidebar > div.opc-block-summary > table > tbody > tr.totals.balance > td > span").text().replace("$", "").replace(",", "").replace("-", "");
            }

            if(window.numPayments == 0) {
                window.numPayments = 12;
                window.paymentAmount = parseFloat(window.creditLimit) / 12;
            }

            var theRisaURL = "/risa.php?buyer=" + myArray[1] + " " + myArray[2] + "&total_cost=" + window.creditLimit + "&num_payments=" + window.numPayments + "&payment_amount=" + window.paymentAmount + "&due_date=weekly&total_price=" + window.creditLimit + "&sales_tax=0&address=" + myArray[4] + ", " + myArray[5];

            window.risaURL = theRisaURL;


            if(window.setRisaSet == 0) {

                var setRisaURL = "/setRisa.php?risa=risa.php?buyer=" + myArray[1] + " " + myArray[2] + "___total_cost=" + window.creditLimit + "___num_payments=" + window.numPayments + "___payment_amount=" + window.paymentAmount + "___due_date=weekly___total_price=" + window.creditLimit + "___sales_tax=0___address=" + myArray[4] + ", " + myArray[5];

                jQuery.get(setRisaURL, function(data) {});

                window.setRisaSet = 1;
                window.setRisaURL = setRisaURL;
            }


            jQuery("div.checkout-agreements-block > div > div > div > label").on("click", function(e) {
                e.preventDefault();

                if(window.doingTheClick == 0) {

                    window.doingTheClick = 1;

                    setTimeout(function() { window.doingTheClick = 0; }, 5000);

                    $.get(window.setRisaURL, function(data) {
                        $.get(window.risaURL, function(data) {
                            $("div.checkout-agreements-item-content").html(data);
                        });
                    });
                }

            });
        }

        var checkLastExist = setInterval(function() {
            if ($('div.checkout-agreements-block > div > div > div > label').length) {
               // clearInterval(checkLastExist);
                doAgreeClick();
            }
        }, 1000);

    });
});