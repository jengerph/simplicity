from django import forms
PAYMENT_REDIRECT_URL = 'http://localhost:8000/results/'

TITLE_CHOICES = (('Mr','Mr.'),
                 ('Ms.','Ms.'),
                 ('Mrs.','Mrs.'),
                 ('Miss','Miss'),
                 ('Dr','Dr.'),
                 ('Sir','Sir.'),
                 ('Prof','Prof.'),)

METHOD_CHOICES = (('CreateTokenCustomer','CreateTokenCustomer'),
                  ('ProcessPayment','ProcessPayment'),
                  ('UpdateTokenCustomer','UpdateTokenCustomer'),
                  ('TokenPayment','TokenPayment'),)


class RequestForm(forms.Form):
    txtRedirectURL = forms.URLField(required=True, 
        initial=PAYMENT_REDIRECT_URL)
    txtAmount = forms.CharField(required=True,
        initial='100')
    txtCurrencyCode = forms.CharField(required=False,
        max_length=10, initial='AUD')
    txtInvoiceNumber = forms.CharField(required=False,
        max_length=80, initial='Inv 21540')
    txtInvoiceReference = forms.CharField(required=False,
        max_length=80, initial='513456')
    txtInvoiceDescription = forms.CharField(required=False,
        initial='Individual Invoice Description')
    txtOption1 = forms.CharField(required=False,
        max_length=10, initial='Option1')
    txtOption2 = forms.CharField(required=False,
        max_length=10, initial='Option2')
    txtOption3 = forms.CharField(required=False,
        max_length=10, initial='Option3')

    # Customer details
    txtTokenCustomerID = forms.CharField(required=False)
    ddlTitle = forms.ChoiceField(required=True,
        label="Title", choices=TITLE_CHOICES, widget=forms.Select())
    txtCustomerRef = forms.CharField(required=False,
        max_length=80, initial='A12345')
    txtFirstName = forms.CharField(max_length=80, required=True, initial='Jonh')
    txtLastName = forms.CharField(max_length=80, required=True, initial='Doe')
    txtCompanyName = forms.CharField(max_length=256, required=False, 
       initial='WEB ACTIVE')
    txtJobDescription = forms.CharField(initial='Developer')

    # Cutomer Address
    txtStreet = forms.CharField(max_length=256, required=False, initial='15 Smith St')
    txtCity = forms.CharField(max_length=80, required=False, initial='Philipp')
    txtState = forms.CharField(max_length=80, required=False, initial='ACT')
    txtPostalCode = forms.CharField(max_length=80, required=False, initial='2602')
    txtCountry = forms.CharField(max_length=2, required=False, initial='au')
    txtEmail = forms.EmailField(required=False, initial='sales@eway.com.au')
    txtPhone = forms.CharField(max_length=20, required=False, initial='1800 10 10 65')
    txtMobile = forms.CharField(max_length=20, required=False, initial='1800 10 10 65')
    txtFax = forms.CharField(max_length=20, required=False, initial='02 9852 2244')
    txtUrl = forms.URLField(required=False, initial='http://www.yoursite.com')
    txtComments = forms.CharField(widget=forms.Textarea, initial='Some comments here')

    # Method
    ddlMethod = forms.ChoiceField(label="Method", choices=METHOD_CHOICES, widget=forms.Select(), required=True)
