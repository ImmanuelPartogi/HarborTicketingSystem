import 'dart:convert';

class Passenger {
  final int? id;
  final String name;
  final String identityType;
  final String identityNumber;
  final String? phone;
  final String? email;
  final String? dateOfBirth;
  final String? gender;
  final String? address;
  final bool isInfant;
  final String? nationality;
  
  // Tambahkan field untuk tiket
  final int? ticketId;
  final String? seatNumber;

  Passenger({
    this.id,
    required this.name,
    required this.identityType,
    required this.identityNumber,
    this.phone,
    this.email,
    this.dateOfBirth,
    this.gender,
    this.address,
    this.isInfant = false,
    this.nationality = 'Indonesia',
    this.ticketId,
    this.seatNumber,
  });

  Passenger copyWith({
    int? id,
    String? name,
    String? identityType,
    String? identityNumber,
    String? phone,
    String? email,
    String? dateOfBirth,
    String? gender,
    String? address,
    bool? isInfant,
    String? nationality,
    int? ticketId,
    String? seatNumber,
  }) {
    return Passenger(
      id: id ?? this.id,
      name: name ?? this.name,
      identityType: identityType ?? this.identityType,
      identityNumber: identityNumber ?? this.identityNumber,
      phone: phone ?? this.phone,
      email: email ?? this.email,
      dateOfBirth: dateOfBirth ?? this.dateOfBirth,
      gender: gender ?? this.gender,
      address: address ?? this.address,
      isInfant: isInfant ?? this.isInfant,
      nationality: nationality ?? this.nationality,
      ticketId: ticketId ?? this.ticketId,
      seatNumber: seatNumber ?? this.seatNumber,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'identity_type': identityType,
      'identity_number': identityNumber,
      'phone': phone,
      'email': email,
      'date_of_birth': dateOfBirth,
      'gender': gender,
      'address': address,
      'is_infant': isInfant ? 1 : 0,
      'nationality': nationality,
      'ticket_id': ticketId,
      'seat_number': seatNumber,
    };
  }

  factory Passenger.fromMap(Map<String, dynamic> map) {
    return Passenger(
      id: map['id']?.toInt(),
      name: map['name'] ?? '',
      identityType: map['identity_type'] ?? 'ktp',
      identityNumber: map['identity_number'] ?? '',
      phone: map['phone'],
      email: map['email'],
      dateOfBirth: map['date_of_birth'],
      gender: map['gender'],
      address: map['address'],
      isInfant: map['is_infant'] == 1 || map['is_infant'] == true,
      nationality: map['nationality'] ?? 'Indonesia',
      ticketId: map['ticket_id']?.toInt(),
      seatNumber: map['seat_number'],
    );
  }

  String toJson() => json.encode(toMap());

  factory Passenger.fromJson(String source) => Passenger.fromMap(json.decode(source));

  @override
  String toString() {
    return 'Passenger(id: $id, name: $name, identityType: $identityType, identityNumber: $identityNumber, phone: $phone, email: $email, dateOfBirth: $dateOfBirth, gender: $gender, address: $address, isInfant: $isInfant, nationality: $nationality, ticketId: $ticketId, seatNumber: $seatNumber)';
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
  
    return other is Passenger &&
      other.id == id &&
      other.name == name &&
      other.identityType == identityType &&
      other.identityNumber == identityNumber &&
      other.phone == phone &&
      other.email == email &&
      other.dateOfBirth == dateOfBirth &&
      other.gender == gender &&
      other.address == address &&
      other.isInfant == isInfant &&
      other.nationality == nationality &&
      other.ticketId == ticketId &&
      other.seatNumber == seatNumber;
  }

  @override
  int get hashCode {
    return id.hashCode ^
      name.hashCode ^
      identityType.hashCode ^
      identityNumber.hashCode ^
      phone.hashCode ^
      email.hashCode ^
      dateOfBirth.hashCode ^
      gender.hashCode ^
      address.hashCode ^
      isInfant.hashCode ^
      nationality.hashCode ^
      ticketId.hashCode ^
      seatNumber.hashCode;
  }
}