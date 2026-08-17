# Güvenlik Politikası

Ografi, kullanıcıların ve platform verilerinin güvenliğini ciddiye alır. Bir güvenlik açığı bulduğunuzu düşünüyorsanız lütfen aşağıdaki sorumlu bildirim sürecini izleyin.

## Desteklenen sürümler

Ografi sürekli dağıtılan bir web uygulamasıdır. Yalnızca `main` dalındaki güncel sürüm ve ografi.com üzerinde çalışan güncel üretim sürümü güvenlik güncellemeleri alır.

| Sürüm | Destek durumu |
| --- | --- |
| Güncel `main` / üretim sürümü | Destekleniyor |
| Eski commitler, forklar ve değiştirilmiş kurulumlar | Desteklenmiyor |

## Güvenlik açığı bildirme

Güvenlik açıklarını herkese açık issue, tartışma, yorum veya sosyal medya gönderisi olarak paylaşmayın.

Raporunuzu GitHub deposundaki **Security** bölümünden özel olarak gönderin:

[Özel güvenlik açığı bildirimi oluştur](https://github.com/Ograficom/topluluk2/security/advisories/new)

Özel bildirim özelliğine erişemiyorsanız, hassas teknik ayrıntıları yayımlamadan depo sahibiyle iletişim kurun ve güvenli bir iletişim kanalı isteyin.

## Raporda bulunması gerekenler

Mümkünse aşağıdaki bilgileri ekleyin:

- Etkilenen URL, özellik, endpoint veya bileşen
- Açığın türü ve olası etkisi
- Tekrarlanabilir adımlar
- Kavram kanıtı, ekran görüntüsü veya ilgili istek/yanıt örneği
- Testte kullanılan tarayıcı, işletim sistemi ve hesap türü
- Varsa önerilen çözüm veya azaltma yöntemi
- Açığın daha önce üçüncü taraflarla paylaşılıp paylaşılmadığı

Parola, oturum çerezi, API anahtarı veya ilgisiz kişisel veri göndermeyin. Gerekli hassas değerleri maskeleyin.

## Yanıt süreci

Raporlar önem ve etkiye göre değerlendirilir. Hedeflenen süreç şöyledir:

1. Bildirimin alındığını 72 saat içinde doğrulamak.
2. İlk değerlendirme sonucunu mümkün olduğunda 7 gün içinde paylaşmak.
3. Geçerli açık için etki, çözüm ve yayın planını belirlemek.
4. Düzeltme yayımlandıktan sonra bildirim sahibine sonuç hakkında bilgi vermek.

Karmaşık sorunlarda süre değişebilir. İnceleme sırasında ek bilgi veya yeniden test istenebilir.

## Kapsam

Aşağıdaki güvenlik sorunları kapsam dahilindedir:

- Kimlik doğrulama veya yetkilendirme atlatma
- Başka kullanıcıların verilerine yetkisiz erişim
- SQL enjeksiyonu, komut enjeksiyonu, SSRF ve benzeri enjeksiyonlar
- Kalıcı veya yansıtılmış XSS
- Etkili CSRF açıkları
- Güvensiz dosya yükleme veya dosya erişimi
- Oturum, parola sıfırlama ve hesap ele geçirme sorunları
- Gizli anahtarların veya hassas yapılandırmanın açığa çıkması
- Anlamlı güvenlik etkisi bulunan bağımlılık açıkları

## Kapsam dışı çalışmalar

Aşağıdaki işlemleri yapmayın:

- Hizmet engelleme, trafik yükleme veya kaynak tüketme testleri
- Otomatik tarayıcılarla yoğun ya da tekrarlayan istek gönderme
- Sosyal mühendislik, kimlik avı veya fiziksel güvenlik testleri
- Size ait olmayan hesaplara erişme veya bu hesapları değiştirme
- Gerekli olandan fazla veri görüntüleme, indirme veya saklama
- Üçüncü taraf hizmetleri, altyapıları veya bağımsız projeleri test etme
- Düzeltme yayımlanmadan açığı ya da kavram kanıtını kamuya açıklama

Yalnızca kendi hesaplarınızı ve verilerinizi kullanın. Bir testin kullanıcıları veya üretim hizmetini etkileyebileceğini fark ederseniz testi hemen durdurun.

## Sorumlu açıklama ve iyi niyet

Bu politikaya uyan, iyi niyetli ve hizmete zarar vermeyen araştırmalar için Ografi, bildirimi sorumlu güvenlik araştırması olarak değerlendirmeyi amaçlar. Açığın kamuya açıklanma zamanını proje yöneticileriyle birlikte belirleyin ve düzeltme için makul süre tanıyın.

Bu politika ödül programı veya ödeme taahhüdü oluşturmaz.
