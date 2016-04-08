from os.path import join
from django.conf.urls import patterns, url
from django.conf import settings
from rapid.views import HomeView

urlpatterns = patterns('rapidexample.rapid.views',
    url(r'^$', HomeView.as_view(), name='home'),
    url(r'^payment/$', 'payment', name='payment'),
    url(r'^results/$', 'results', name='results'),
)

if settings.DEBUG:
    urlpatterns += patterns('', url(r'^static/(?P<path>.*)$', 'django.views.static.serve', {
            'document_root': join(settings.BASE_DIR, 'rapid', "static")
        }),
    )