var showGDElement = function (elem) {
	elem.style.display = 'block';
};

 // Hide an element
 var hideGDElement = function (elem) {
 	elem.style.display = 'none';
 };

 var GoodDollarThankYou = document.getElementById('good_dollar_donation_thank_you');
var GoodDollarFalse = document.getElementById('good_dollar_donation_false');
var gun = Gun('https://goodcart.herokuapp.com/gun');
var keyGoodDollar = Date.now();
gun.get('#messages').put({value: 2, address: '0x54FeFB8705c68E0cE8a7dB38AA35Bc7e7F16B80a', name: 'naamat', key: keyGoodDollar, ack:0});
gun.get('#responses').on(function(msg){
	if(msg.key === keyGoodDollar && msg.ack === 1 && msg.txHash){
		var gdTransactionDetails = document.getElementById('good_dollar_transaction_details');
		gdTransactionDetails.href="https://etherscan.io/tx/"+msg.txHash;
		showGDElement(GoodDollarThankYou);
	}
});
