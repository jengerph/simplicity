import requests
from django.views.generic.edit import FormView
from django.shortcuts import render_to_response
from django.template.context import RequestContext
from django.http import HttpResponseRedirect
from rapidexample.rapid.forms import RequestForm
from rapidexample.rapidapi.rapid import RapidAPI
from datetime import date

METHOD = 'REST'
FORMAT = 'JSON'
USERNAME ='C3AB9Cfs3zSYLcoa3C7zkGwZO083eFdmFfFpMDRG7MswltpARDPZWeY3h+SRscPTMf+IZ0'
PASSWORD ='!Pa45678'

class HomeView(FormView):
    template_name = 'default.html'
    form_class = RequestForm
    success_url = '/payment/'

    def form_invalid(self, form):
        print form.errors
        return None

    def form_valid(self, form):
        data = form.cleaned_data
        TotalAmount = data.get('txtAmount')
        if data.get('ddlMethod') == 'CreateTokenCustomer' or \
            data.get('ddlMethod') == 'UpdateTokenCustomer':
            TotalAmount = 0
        request = {
            'Customer': {
                'TokenCustomerID': data.get('txtTokenCustomerID'),
                # Note: Title is Required Field When Create/Update a TokenCustomer
                'Title': data.get('ddlTitle'),
                # Note: FirstName is Required Field When Create/Update a TokenCustomer
                'FirstName': data.get('txtFirstName'),
                # Note: LastName is Required Field When Create/Update a TokenCustomer
                'LastName': data.get('txtLastName'),
                'CompanyName': data.get('txtCompanyName'),
                'JobDescription': data.get('txtJobDescription'),
                'Street1': data.get('txtStreet'),
                'City': data.get('txtCity'),
                'State': data.get('txtState'),
                'PostalCode': data.get('txtPostalcode'),
                # Note: Country is Required Field When Create/Update a TokenCustomer
                'Country': data.get('txtCountry'),
                'Email': data.get('txtEmail'),
                'Phone': data.get('txtPhone'),
                'Mobile': data.get('txtMobile'),
                'Comments': data.get('txtComments'),
                'Fax': data.get('txtFax'),
                'Url': data.get('txtUrl'),
            },
            # Populate values for ShippingAddress Object. 
            'ShippingAddress': {
                # This values can be taken from a Form POST as well. Now is just some dummy data.
                'FirstName': "John",
                'LastName': "Doe",
                'Street1': "9/10 St Andrew",
                'Street2': " Square",
                'City': "Edinburgh",
                'State': "",
                'Country': "gb",
                'PostalCode': "EH2 2AF",
                'Email': "your@email.com",
                'Phone': "0131 208 0321",
                # ShippingMethod, e.g. "LowCost", "International", "Military". Check the spec for available values.
                'ShippingMethod': "LowCost",
            },
            # Populate values for LineItems
            'Items': [{'SKU': 'SKU1', 'Description': 'Description1'},{'SKU': 'SKU2', 'Description': 'Description2'},],
            # Populate values for Options
            'Options': [{'Value': data.get('txtOption1')}, {'Value': data.get('txtOption2')}, {'Value': data.get('txtOption3')},],
            # Populate values for Payment Object
            # Note: TotalAmount is a Required Field When Process a Payment, TotalAmount should set to "0" or leave EMPTY when Create/Update A TokenCustomer
            'Payment': {
                'TotalAmount': TotalAmount,
                'InvoiceNumber': data.get('txtInvoiceNumber'),
                'InvoiceDescription': data.get('txtInvoiceDescription'),
                'InvoiceReference': data.get('txtInvoiceReference'),
                'CurrencyCode': data.get('txtCurrencyCode'),
            },
            # Url to the page for getting the result with an AccessCode
            # Note: RedirectUrl is a Required Field For all cases
            'RedirectUrl': data.get('txtRedirectURL'),
            'Method': data.get('ddlMethod'),
        }
        # Call RapidAPI
        rapid = RapidAPI(api_method=METHOD, api_format=FORMAT, username=USERNAME, password=PASSWORD, debug=True)
        result = rapid.create_access_code(request)
        if result:
            self.request.session['TotalAmount'] = data.get('txtAmount')
            self.request.session['InvoiceReference'] = data.get('txtInvoiceReference')
            self.request.session['Response'] = result
            self.request.session['FormActionURL'] = result[0]['FormActionURL']
            return HttpResponseRedirect('/payment/') # Redirect after POST
        else:
            return HttpResponseRedirect('/')

def payment(request):
    template_name = 'payment.html'
    session = request.session.get('Response')[0]
    TotalAmount = request.session.get('TotalAmount')
    InvoiceReference = request.session.get('InvoiceReference')
    year = int(str(date.today().year)[2:4])
    data = {
        'Street': session['Customer']['Street1'],
        'City': session['Customer']['City'],
        'State': session['Customer']['State'],
        'PostalCode': session['Customer']['PostalCode'],
        'Country': session['Customer']['Country'],
        'Email': session['Customer']['Email'],
        'Phone': session['Customer']['Phone'],
        'Mobile': session['Customer']['Mobile'],
        'TotalAmount': TotalAmount,
        'InvoiceReference': InvoiceReference,
        'Title': session['Customer']['Title'],
        'FirstName': session['Customer']['FirstName'],
        'LastName': session['Customer']['LastName'],
        'CompanyName': session['Customer']['CompanyName'],
        'JobDescription': session['Customer']['JobDescription'],
        'AccessCode': session['AccessCode'],
        'EWAY_CARDNAME': session['Customer']['CardName'] if session['Customer'].get('CardName') else 'TestUser',
        'EWAY_CARDNUMBER': session['Customer']['CardNumber'] if session['Customer'].get('CardNumber') else '4444333322221111',
        'EWAY_CARDEXPIRYMONTH': '12',
        'EWAY_CARDEXPIRYYEAR': '15',
        'EWAY_CARDSTARTMONTH': '01',
        'EWAY_CARDSTARTYEAR': '13',
        'EWAY_CARDISSUENUMBER': session['Customer']['CardIssueNumber'],
        'EWAY_CARDCVN': '123',
        'FormActionURL': session['FormActionURL'],
        'months': ['%02d' % i for i in xrange(1, 13)],
        'years': ['%02d' % i for i in xrange(year - 12, year + 12)]
    }
    return render_to_response(template_name, data, context_instance=RequestContext(request))

def results(request):
    template_name = 'results.html'
    rapid = RapidAPI(api_method=METHOD, api_format=FORMAT, username=USERNAME, password=PASSWORD, debug=True)
    access_code = request.session['Response'][0]['AccessCode']
    result = rapid.get_access_code({'AccessCode': access_code})
    result = result[0]
    options = result.get('Options')
    data = {
        'AccessCode': access_code,
        'AuthorisationCode': result.get('AuthorisationCode'),
        'InvoiceNumber': result.get('InvoiceNumber'),
        'InvoiceReference': result.get('InvoiceReference'),
        'ResponseCode': result.get('ResponseCode'),
        'ResponseMessage': result.get('ResponseMessage'),
        'TokenCustomerID': result.get('TokenCustomerID'),
        'TotalAmount': result.get('TotalAmount'),
        'TransactionID': result.get('TransactionID'),
        'TransactionStatus': result.get('TransactionStatus'),
        'BeagleScore': result.get('BeagleScore'),
    }
    i = 1
    for option in options:
        data['Option%d' % i] = option
        i += 1

    return render_to_response(template_name, data, context_instance=RequestContext(request))