import i18n from 'i18next'; import {initReactI18next} from 'react-i18next';
import de from '../../locales/de/common.json'; import ar from '../../locales/ar/common.json'; import en from '../../locales/en/common.json'; import tr from '../../locales/tr/common.json'; import uk from '../../locales/uk/common.json';
i18n.use(initReactI18next).init({resources:{de:{translation:de},ar:{translation:ar},en:{translation:en},tr:{translation:tr},uk:{translation:uk}},lng:'de',fallbackLng:'de',interpolation:{escapeValue:false}});
i18n.on('languageChanged',(lng)=>{document.documentElement.lang=lng;document.documentElement.dir=lng==='ar'?'rtl':'ltr';}); export default i18n;
