# pay
Pay Project

Örnek Test dosyasında request ve response'lar verilmiştir

Request için 
Sipariş bilgileri
Kart bilgileri
Banka bilgileri
Url bilgileri
new Class oluşturularak oluşturulmalıdır

Kart bilgilerinde setType için 1 - Mastercard 2- VISA dır
Default ayar bilgileri Data/settings.json da tutulmaktadır

Ayarları özelleştirmek için Banka Classından setSettings fonksiyonuna değiştirmek istediğiniz key ve value bilgisiyle gidebilirsiniz

Response için projenizde routelar oluşturmalısınız, bu oluşturulacak urller başarılı yada başarısız olarak requestte gönderilecektir
örnek olarak www.ornek.com/sonuc/bankatipi/taksitsayısı şeklinde olmalıdır
gelen bankatipi ve taksit sayısını otomatik olarak response classına göndererek sonucunu alabilirsiniz

Response genelinde api bilgilerinizi dahil etmeniz lazım bu yüzden banka bilgilerini girmek zorunludur ve taksit sayısınıda responseye göndermeniz yeterli olacaktır
