<?php

namespace App\Traits;

trait GerentiSQL
{
    public function busca()
    {
        return '
         
        WITH PARAMETROS AS
        (
            SELECT
                CAST(:registro AS VARCHAR(20))  AS REGISTRO,
                CAST(:nome AS VARCHAR(100)) AS NOME,
                CAST(:cpfCnpj AS VARCHAR(20))  AS CPFCNPJ,
                CAST(:email AS VARCHAR(80))  AS EMAIL,
                CAST(:telefone AS VARCHAR(80))  AS TELEFONE,
                CAST(:regional AS VARCHAR(50))  AS REGIONAL,
                CAST(:municipio AS VARCHAR(100)) AS MUNICIPIO,
                CAST(:anoCadastro AS VARCHAR(4)) AS ANOCADASTRO
            FROM RDB$DATABASE
        )
         
        SELECT FIRST 1000 DISTINCT
                  
            A.ASS_ID,
            A.ASS_ATIVO,
            A.ASS_ENTIDADE,
            A.ASS_TP_ASSOC,
            A.ASS_REGISTRO,
            A.ASS_NOME,
            A.ASS_CPF_CGC,
            A.ASS_DT_ADMISSAO,
            A.ASS_DT_REG_SOCIAL,
            A.ASS_TP_PESSOA,
            A.ASS_USU_CADASTRO,
            A.ASS_TP_REGIAO,
            A.ASS_DT_UPDATE,
            A.USU_CODIGO,
            A.SYS_LAST_UPDATE,
         
         
            CASE
                WHEN A.ASS_TP_ASSOC = 1
         
                 AND COALESCE(
                        (
                            SELECT FIRST 1 TRIM(D.DAD_VALOR)
                            FROM DADOS D
         
                            INNER JOIN TP_PARAMETRO TP
                                ON TP.TPP_ID = D.TPD_ID
         
                            WHERE TP.TPP_CODIGO = \'Tipo_Empresa\'
                              AND D.ASS_ID = A.ASS_ID
                        ),
                        \'\'
                     ) <> \'\'
         
                 AND COALESCE(
                        (
                            SELECT FIRST 1 TRIM(D.DAD_VALOR)
                            FROM DADOS D
         
                            INNER JOIN TP_PARAMETRO TP
                                ON TP.TPP_ID = D.TPD_ID
         
                            WHERE TP.TPP_CODIGO = \'Tipo_Empresa\'
                              AND D.ASS_ID = A.ASS_ID
                        ),
                        \'\'
                     ) <> TRIM(T.TPA_DESCRICAO)
         
                THEN
                    TRIM(T.TPA_DESCRICAO)
                    || \' (\'
                    || COALESCE(
                        (
                            SELECT FIRST 1 TRIM(D.DAD_VALOR)
                            FROM DADOS D
         
                            INNER JOIN TP_PARAMETRO TP
                                ON TP.TPP_ID = D.TPD_ID
         
                            WHERE TP.TPP_CODIGO = \'Tipo_Empresa\'
                              AND D.ASS_ID = A.ASS_ID
                        ),
                        \'\'
                    )
                    || \')\'
         
                ELSE TRIM(T.TPA_DESCRICAO)
            END
         
            ||
         
            CASE
                WHEN
                    (
                        SELECT FIRST 1 TRIM(EMP.EMP_NOME_ABREV)
                        FROM EMPRESA EMP
                    ) = \'CORE-RJ\'
         
                AND EXISTS
                    (
                        SELECT 1
                        FROM DADOS D1
         
                        INNER JOIN TP_PARAMETRO TP1
                            ON TP1.TPP_ID = D1.TPD_ID
         
                        WHERE D1.ASS_ID = A.ASS_ID
                          AND TP1.TPP_CODIGO = \'OUTROCORE\'
                          AND D1.DAD_VALOR = \'SIM\'
                    )
         
                AND EXISTS
                    (
                        SELECT 1
                        FROM DADOS D2
         
                        INNER JOIN TP_PARAMETRO TP2
                            ON TP2.TPP_ID = D2.TPD_ID
         
                        WHERE D2.ASS_ID = A.ASS_ID
                          AND TP2.TPP_CODIGO = \'qual_core\'
                          AND D2.DAD_VALOR = \'CORE-GB\'
                    )
         
                THEN \' (EX-CORE)\'
         
                ELSE \'\'
            END AS TIPO,
         
            CASE
                WHEN COALESCE(
                        (
                            SELECT FIRST 1 VC.STATUS
                            FROM VWCANCELADOS VC
                            WHERE VC.ASS_ID = A.ASS_ID
                        ),
                        0
                     ) = 1
         
                THEN
                    CASE
                        WHEN
                            (
                                SELECT FIRST 1 TRIM(EMP.EMP_NOME_ABREV)
                                FROM EMPRESA EMP
                            ) = \'CORE-RS\'
         
                        AND
                            (
                                SELECT FIRST 1 EV.EVE_TIPO
                                FROM EVENTOS EV
         
                                INNER JOIN TP_EVENTO TE
                                    ON TE.TPE_ID = EV.EVE_TIPO
         
                                WHERE EV.EVE_ASS_ID = A.ASS_ID
                                  AND TE.CANCELADO = 1
                                  AND EV.EVE_STATUS = 0
         
                                ORDER BY
                                    EV.EVE_DATA_INI DESC,
                                    EV.EVE_ID DESC
                            ) IN (21, 99, 100, 101, 123, 124, 125)
         
                        THEN \'F\'
         
                        ELSE \'T\'
                    END
         
                ELSE \'F\'
            END AS CANCELADO,
         
            CASE
                WHEN
                    (
                        SELECT FIRST 1 TRIM(EMP.EMP_NOME_ABREV)
                        FROM EMPRESA EMP
                    ) = \'CORE-RJ\'
         
                AND EXISTS
                    (
                        SELECT 1
                        FROM DADOS D1
         
                        INNER JOIN TP_PARAMETRO TP1
                            ON TP1.TPP_ID = D1.TPD_ID
         
                        WHERE D1.ASS_ID = A.ASS_ID
                          AND TP1.TPP_CODIGO = \'OUTROCORE\'
                          AND D1.DAD_VALOR = \'SIM\'
                    )
         
                AND EXISTS
                    (
                        SELECT 1
                        FROM DADOS D2
         
                        INNER JOIN TP_PARAMETRO TP2
                            ON TP2.TPP_ID = D2.TPD_ID
         
                        WHERE D2.ASS_ID = A.ASS_ID
                          AND TP2.TPP_CODIGO = \'qual_core\'
                          AND D2.DAD_VALOR = \'CORE-GB\'
                    )
         
                THEN 1
         
                ELSE 0
            END AS EXCORE,
         
            
            A.REGIONALREPRESENTANTE,
         
            (
                SELECT FIRST 1 TRIM(E.END_MUNICIPIO)
                FROM ENDERECOS E
                WHERE E.ASS_ID = A.ASS_ID
                  AND E.END_CORRESP = \'T\'
            ) AS MUNICIPIO,
         
            CASE
                WHEN CHAR_LENGTH(TRIM(A.ASS_REGISTRO)) >= 4
         
                THEN SUBSTRING(
                    TRIM(A.ASS_REGISTRO)
                    FROM CHAR_LENGTH(TRIM(A.ASS_REGISTRO)) - 3
                    FOR 4
                )
         
                ELSE NULL
            END AS ANOCADASTRO
         
        FROM ASSOCIADOS A
         
        INNER JOIN TP_ASSOC T
            ON T.TPA_ID = A.ASS_TP_ASSOC
         
        CROSS JOIN PARAMETROS PAR
         
        WHERE 1 = 1
         
         
            AND
            (
                PAR.REGISTRO = \'\'
         
                OR A.ASS_REGISTRO STARTING WITH PAR.REGISTRO
            )
         
            
            AND
            (
                PAR.NOME = \'\'
         
                OR UPPER(A.ASS_NOME) STARTING WITH UPPER(PAR.NOME)
            )
         
            
            AND
            (
                PAR.CPFCNPJ = \'\'
         
                OR A.ASS_CPF_CGC STARTING WITH PAR.CPFCNPJ
         
                OR
                (
                    CHAR_LENGTH(TRIM(PAR.CPFCNPJ)) <> 14
         
                    AND
                    (
         
                        (
                            (
                                SELECT FIRST 1 TRIM(EMP.EMP_NOME_ABREV)
                                FROM EMPRESA EMP
                            ) <> \'CORE-BA\'
         
                            AND EXISTS
                            (
                                SELECT 1
                                FROM RELASSOCIADOS R
         
                                WHERE R.REL_ASSOCIADO_PAI = A.ASS_ID
         
                                  AND R.REL_CPF_FILHO
                                      STARTING WITH PAR.CPFCNPJ
         
                                  AND
                                  (
                                      (
                                          SELECT FIRST 1
                                                 TRIM(EMP.EMP_NOME_ABREV)
                                          FROM EMPRESA EMP
                                      ) = \'CORE-MS\'
         
                                      OR R.REL_TP_RELACAO
                                         IN (1, 2, 7, 100)
                                  )
         
                                  AND
                                  (
                                      PAR.NOME = \'\'
         
                                      OR UPPER(R.REL_NOME)
                                         STARTING WITH UPPER(PAR.NOME)
         
                                      OR UPPER(A.ASS_NOME)
                                         STARTING WITH UPPER(PAR.NOME)
                                  )
                            )
                        )
         
                        OR
         
         
                        (
                            (
                                SELECT FIRST 1 TRIM(EMP.EMP_NOME_ABREV)
                                FROM EMPRESA EMP
                            ) = \'CORE-BA\'
         
                            AND EXISTS
                            (
                                SELECT 1
                                FROM DADOS DBA
         
                                WHERE DBA.ASS_ID = A.ASS_ID
                                  AND DBA.TPD_ID = 51
         
                                  AND REPLACE(
                                          DBA.DAD_VALOR,
                                          \'-\',
                                          \'\'
                                      ) STARTING WITH PAR.CPFCNPJ
                            )
                        )
                    )
                )
            )
         
         
            AND
            (
                PAR.EMAIL = \'\'
         
                OR EXISTS
                (
                    SELECT 1
                    FROM CONTATOXPESS CE
         
                    WHERE CE.CXP_ASS_ID = A.ASS_ID
                      AND CE.CXP_TIPO = 3
         
                      AND UPPER(CE.CXP_VALOR)
                          CONTAINING UPPER(PAR.EMAIL)
                )
            )
         
            AND
            (
                PAR.TELEFONE = \'\'
         
                OR EXISTS
                (
                    SELECT 1
                    FROM CONTATOXPESS CT
         
                    WHERE CT.CXP_ASS_ID = A.ASS_ID
                      AND CT.CXP_TIPO NOT IN (3, 5)
         
                      AND CT.CXP_VALOR
                          CONTAINING PAR.TELEFONE
                )
            )
         
            
            AND
            (
                PAR.REGIONAL = \'\'
         
                OR UPPER(TRIM(A.REGIONALREPRESENTANTE))
                   = UPPER(TRIM(PAR.REGIONAL))
            )
         
           
        AND
        (
            PAR.MUNICIPIO = \'\'
         
            OR EXISTS
            (
                SELECT 1
                FROM ENDERECOS EM
                WHERE EM.ASS_ID = A.ASS_ID
                  AND EM.END_CORRESP = \'T\'
         
                  AND
         
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                      UPPER(TRIM(EM.END_MUNICIPIO)),
                      \'Á\',\'A\'),
                      \'À\',\'A\'),
                      \'Ã\',\'A\'),
                      \'Â\',\'A\'),
                      \'É\',\'E\'),
                      \'Ç\',\'C\'),
                      \'Ó\',\'O\')
         
                  STARTING WITH
         
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                  REPLACE(
                      UPPER(TRIM(PAR.MUNICIPIO)),
                      \'Á\',\'A\'),
                      \'À\',\'A\'),
                      \'Ã\',\'A\'),
                      \'Â\',\'A\'),
                      \'É\',\'E\'),
                      \'Ç\',\'C\'),
                      \'Ó\',\'O\')
            )
        )
         
         
            AND
            (
                PAR.ANOCADASTRO = \'\'
         
                OR
                (
                    CHAR_LENGTH(TRIM(A.ASS_REGISTRO)) >= 4
         
                    AND SUBSTRING(
                            TRIM(A.ASS_REGISTRO)
                            FROM CHAR_LENGTH(TRIM(A.ASS_REGISTRO)) - 3
                            FOR 4
                        ) = PAR.ANOCADASTRO
                )
            )
         
        ORDER BY A.ASS_REGISTRO
         
        ';
    }
}